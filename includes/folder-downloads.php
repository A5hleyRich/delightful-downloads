<?php
/**
 * Folder downloads for Delightful Downloads.
 * A dedo_download can point to a directory instead of a single file.
 */
defined( 'ABSPATH' ) || exit;

function dedo_is_folder_download( $post_id ) {
    return 'folder' === get_post_meta( (int) $post_id, '_dedo_source_type', true );
}

function dedo_folder_upload_root() {
    $uploads = wp_upload_dir();
    $basedir = isset( $uploads['basedir'] ) ? realpath( $uploads['basedir'] ) : false;
    return $basedir ? wp_normalize_path( $basedir ) : '';
}

function dedo_folder_path( $post_id ) {
    $raw = trim( (string) get_post_meta( (int) $post_id, '_dedo_folder_path', true ) );
    if ( '' === $raw ) return '';
    $raw = wp_normalize_path( $raw );
    $root = wp_normalize_path( ABSPATH );
    $candidate = str_starts_with( $raw, $root ) ? $raw : $root . ltrim( $raw, '/' );
    $real = realpath( $candidate );
    if ( ! $real || ! is_dir( $real ) ) return '';
    $real = wp_normalize_path( $real );
    $upload_root = dedo_folder_upload_root();
    if ( ! $upload_root || ( $real !== $upload_root && ! str_starts_with( $real, trailingslashit( $upload_root ) ) ) ) return '';
    return $real;
}

function dedo_folder_upload_subdirectories() {
    $root = dedo_folder_upload_root();
    if ( ! $root || ! is_dir( $root ) ) return array();
    $dirs = array();
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ( $iterator as $item ) {
            if ( ! $item->isDir() || $item->isLink() ) continue;
            $real = wp_normalize_path( $item->getRealPath() );
            if ( ! str_starts_with( $real, trailingslashit( $root ) ) ) continue;
            $relative_upload = ltrim( substr( $real, strlen( $root ) ), '/' );
            if ( '' === $relative_upload ) continue;
            $dirs[ dedo_folder_relative_path( $real ) ] = $relative_upload;
        }
    } catch ( UnexpectedValueException $e ) {
        return array();
    }
    natcasesort( $dirs );
    return $dirs;
}

function dedo_folder_relative_path( $absolute ) {
    $root = wp_normalize_path( realpath( ABSPATH ) ?: ABSPATH );
    $absolute = wp_normalize_path( $absolute );
    return ltrim( substr( $absolute, strlen( rtrim( $root, '/' ) ) ), '/' );
}

function dedo_folder_allowed_extensions() {
    return apply_filters( 'dedo_folder_allowed_extensions', array( 'pdf','doc','docx','xlsx','xls','ppt','pptx','vsd','vsdx','pub','pubx','exe','msi','zip','mp3','mp4','mkv','avi','7z','txt','rar','html','htm','xml','jpg','jpeg','png','gif','webp' ) );
}

function dedo_folder_file_count_key( $relative_file ) {
    return '_dedo_folder_file_count_' . md5( $relative_file );
}

function dedo_folder_description_map( $folder ) {
    static $cache = array();
    if ( isset( $cache[$folder] ) ) return $cache[$folder];
    $cache[$folder] = array();
    $file = trailingslashit( $folder ) . 'descriptions.txt';
    if ( is_readable( $file ) && ( $handle = fopen( $file, 'r' ) ) ) {
        while ( ( $row = fgetcsv( $handle, 0, ',', '"', '' ) ) !== false ) {
            if ( isset( $row[0] ) && '' !== trim( $row[0] ) ) $cache[$folder][trim( $row[0] )] = $row[1] ?? '';
        }
        fclose( $handle );
    }
    return $cache[$folder];
}

function dedo_folder_download_scan( $post_id ) {
    $folder = dedo_folder_path( $post_id );
    if ( ! $folder ) return array();
    $allowed = array_flip( dedo_folder_allowed_extensions() );
    $files = array();
    $template = array( 'filename,description' );
    foreach ( scandir( $folder ) ?: array() as $name ) {
        if ( '.' === $name || '..' === $name || str_starts_with( $name, 'descriptions' ) ) continue;
        $path = trailingslashit( $folder ) . $name;
        $ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
        if ( ! isset( $allowed[$ext] ) || ! is_file( $path ) ) continue;
        $relative = dedo_folder_relative_path( $path );
        $files[] = array(
            'name' => $name,
            'path' => $path,
            'relative' => $relative,
            'size' => (int) ( @filesize( $path ) ?: 0 ),
            'mtime' => (int) ( @filemtime( $path ) ?: 0 ),
            'ctime' => (int) ( @filectime( $path ) ?: 0 ),
            'ext' => $ext,
            'dlds' => (int) get_post_meta( $post_id, dedo_folder_file_count_key( $relative ), true ),
        );
        $template[] = $name . ',';
    }
    $template_file = trailingslashit( $folder ) . 'descriptions-vorlage.txt';
    $template_content = implode( "\n", $template );
    if ( wp_is_writable( $folder ) && ( ! is_file( $template_file ) || file_get_contents( $template_file ) !== $template_content ) ) {
        file_put_contents( $template_file, $template_content );
    }
    return $files;
}

function dedo_folder_sort( &$files, $sort ) {
    if ( 'random' === $sort ) { shuffle( $files ); return; }
    usort( $files, static fn( $a, $b ) => match ( $sort ) {
        'dlds_desc' => $b['dlds'] <=> $a['dlds'],
        'size' => $a['size'] <=> $b['size'],
        'size_desc' => $b['size'] <=> $a['size'],
        'date' => $a['mtime'] <=> $b['mtime'],
        'date_desc' => $b['mtime'] <=> $a['mtime'],
        'filename_desc' => strnatcasecmp( $b['name'], $a['name'] ),
        default => strnatcasecmp( $a['name'], $b['name'] ),
    } );
}

function dedo_folder_icon_color( $ext ) {
    return match ( strtolower( (string) $ext ) ) {
        'pdf','xps' => '#c33', 'doc','docx','odt' => '#0879c9', 'xls','xlsx','ods' => '#14834b',
        'ppt','pptx','pps','ppsx' => '#e76f00', 'vsd','vsdx' => '#3d4cc9', 'zip','rar','7z' => '#db8500',
        'mp3','m4a','wma' => '#8d43ad', 'mp4','mkv','avi','mov','wmv' => '#5d42ad', 'html','htm','xml' => '#008eaa',
        'exe','msi' => '#333', 'txt','md','css','php','js' => '#687278', 'jpg','jpeg','png','gif','webp' => '#c33b82',
        'pub','pubx' => '#008b85', default => '#7a858a',
    };
}

function dedo_folder_access_token_url( $post_id, $valid_days = 7 ) {
    $post_id = absint( $post_id );
    $valid_days = absint( $valid_days );
    if ( ! in_array( $valid_days, array( 7, 365 ), true ) ) return '';
    $today = new DateTimeImmutable( 'today', wp_timezone() );
    $expires = $today->modify( '+' . ( $valid_days - 1 ) . ' days' )->format( 'Ymd' );
    $payload = $post_id . '|' . $valid_days . '|' . $expires . '|folder';
    $token = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
    return add_query_arg( array(
        'folder_access' => $valid_days,
        'folder_expires' => $expires,
        'folder_token' => $token,
    ), get_permalink( $post_id ) );
}

function dedo_folder_access_params_from_request( $post_id ) {
    $post_id = absint( $post_id );
    $valid_days = isset( $_GET['folder_access'] ) ? absint( $_GET['folder_access'] ) : 0;
    $expires = isset( $_GET['folder_expires'] ) ? sanitize_text_field( wp_unslash( $_GET['folder_expires'] ) ) : '';
    $token = isset( $_GET['folder_token'] ) ? sanitize_text_field( wp_unslash( $_GET['folder_token'] ) ) : '';
    if ( ! in_array( $valid_days, array( 7, 365 ), true ) || ! preg_match( '/^\d{8}$/', $expires ) || '' === $token ) return array();
    $expiry = DateTimeImmutable::createFromFormat( '!Ymd', $expires, wp_timezone() );
    if ( ! $expiry || $expiry->format( 'Ymd' ) !== $expires ) return array();
    $today = new DateTimeImmutable( 'today', wp_timezone() );
    $start = $expiry->modify( '-' . ( $valid_days - 1 ) . ' days' );
    if ( $today < $start || $today > $expiry ) return array();
    $expected = hash_hmac( 'sha256', $post_id . '|' . $valid_days . '|' . $expires . '|folder', wp_salt( 'auth' ) );
    if ( ! hash_equals( $expected, $token ) ) return array();
    return array( 'folder_access' => $valid_days, 'folder_expires' => $expires, 'folder_token' => $token );
}

function dedo_folder_access_allowed( $post_id ) {
    $protect = (int) get_post_meta( (int) $post_id, '_dedo_folder_protect', true );
    // Level 0: public/direct. Level 1: routed through the secure endpoint but no token required.
    // Level 2: token required for visitors; editors may always preview their own download.
    if ( $protect < 2 ) return true;
    if ( current_user_can( 'edit_post', (int) $post_id ) ) return true;
    return ! empty( dedo_folder_access_params_from_request( $post_id ) );
}

function dedo_folder_status_icon( $locked, $title = '' ) {
    $locked = (bool) $locked;
    if ( '' === $title ) $title = $locked ? __( 'Protected - token required', 'delightful-downloads' ) : __( 'Download available', 'delightful-downloads' );
    $path = $locked
        ? '<rect x="5" y="10" width="14" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>'
        : '<rect x="5" y="10" width="14" height="10" rx="2"></rect><path d="M16 10V7a4 4 0 0 0-7.5-2"></path>';
    return '<span class="dedo-folder-status-icon" title="' . esc_attr( $title ) . '"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg><span class="screen-reader-text">' . esc_html( $title ) . '</span></span>';
}

function dedo_folder_secure_url( $post_id, $relative_file ) {
    $day = current_time( 'Y-m-d' );
    $sig = hash_hmac( 'sha256', $post_id . '|' . $relative_file . '|' . $day, wp_salt( 'auth' ) );
    $args = array( 'dedo_folder_file' => rawurlencode( $relative_file ), 'download_id' => (int) $post_id, 'sig' => $sig );
    $args = array_merge( $args, dedo_folder_access_params_from_request( $post_id ) );
    return add_query_arg( $args, home_url( '/' ) );
}

function dedo_folder_handle_download() {
    if ( empty( $_GET['dedo_folder_file'] ) || empty( $_GET['download_id'] ) || empty( $_GET['sig'] ) ) return;
    $post_id = absint( $_GET['download_id'] );
    $relative = sanitize_text_field( rawurldecode( wp_unslash( $_GET['dedo_folder_file'] ) ) );
    $sig = sanitize_text_field( wp_unslash( $_GET['sig'] ) );
    $expected = hash_hmac( 'sha256', $post_id . '|' . $relative . '|' . current_time( 'Y-m-d' ), wp_salt( 'auth' ) );
    if ( ! hash_equals( $expected, $sig ) ) dedo_download_abort( __( 'Download not found or ticket invalid or expired.', 'delightful-downloads' ) );

    $path = realpath( ABSPATH . ltrim( $relative, '/\\' ) );
    $root = wp_normalize_path( realpath( ABSPATH ) ?: ABSPATH );
    $is_managed_folder = dedo_download_valid( $post_id ) && dedo_is_folder_download( $post_id );
    $folder = $is_managed_folder ? dedo_folder_path( $post_id ) : '';
    $inside_allowed_root = $path && is_file( $path ) && str_starts_with( wp_normalize_path( $path ), trailingslashit( rtrim( $root, '/' ) ) );
    if ( $is_managed_folder ) $inside_allowed_root = $inside_allowed_root && $folder && str_starts_with( wp_normalize_path( $path ), trailingslashit( wp_normalize_path( $folder ) ) );
    if ( ! $inside_allowed_root || ( ! $is_managed_folder && ! get_post( $post_id ) ) ) dedo_download_abort( __( 'Server error, file cannot be opened!', 'delightful-downloads' ) );

    if ( $is_managed_folder ) {
        if ( 2 === (int) get_post_meta( $post_id, '_dedo_folder_protect', true ) && ! dedo_folder_access_allowed( $post_id ) ) dedo_download_abort( __( 'This folder is protected. A valid access token is required.', 'delightful-downloads' ) );
        $has_folder_token = ! empty( dedo_folder_access_params_from_request( $post_id ) );
        $options = get_post_meta( $post_id, '_dedo_file_options', true );
        // A deliberately shared folder token works like Delightful Downloads' signed tickets:
        // it is the visitor's credential and therefore bypasses member/password gates.
        if ( ! $has_folder_token && ! dedo_download_permission( is_array( $options ) ? $options : array() ) ) dedo_download_abort( __( 'Please login to download this file!', 'delightful-downloads' ) );
        if ( ! $has_folder_token && post_password_required( $post_id ) ) dedo_download_password_prompt( $post_id );
        do_action( 'ddownload_download_before', $post_id );
    }
    update_post_meta( $post_id, dedo_folder_file_count_key( $relative ), (int) get_post_meta( $post_id, dedo_folder_file_count_key( $relative ), true ) + 1 );
    while ( ob_get_level() ) @ob_end_clean();
    nocache_headers();
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Content-Type: ' . dedo_download_mime( $path ) );
    header( 'Content-Length: ' . filesize( $path ) );
    header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( basename( $path ) ) . '"' );
    readfile( $path );
    if ( $is_managed_folder ) do_action( 'ddownload_download_complete', $post_id );
    exit;
}
add_action( 'wp_loaded', 'dedo_folder_handle_download', 1 );

function dedo_folder_protection_for_post( $post_id ) {
    $folder = dedo_folder_path( $post_id );
    if ( ! $folder || ! wp_is_writable( $folder ) ) return;

    $protect = (int) get_post_meta( $post_id, '_dedo_folder_protect', true );
    $htaccess = trailingslashit( $folder ) . '.htaccess';
    $begin = '# BEGIN Delightful Downloads Folder Protection';
    $end = '# END Delightful Downloads Folder Protection';
    $rules = $begin . "\nOptions -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n" . $end;
    $legacy_rules = "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n";
    $content = is_file( $htaccess ) ? (string) file_get_contents( $htaccess ) : '';

    // Migrate the exact unmarked file written by earlier Folder Download builds.
    if ( $content === $legacy_rules ) {
        $content = '';
    }

    $pattern = '/(?:^|\R)' . preg_quote( $begin, '/' ) . '.*?' . preg_quote( $end, '/' ) . '(?:\R|$)/s';
    $content_without_dedo = trim( (string) preg_replace( $pattern, "\n", $content ) );

    if ( $protect ) {
        $new_content = $content_without_dedo === '' ? $rules . "\n" : $content_without_dedo . "\n\n" . $rules . "\n";
        if ( ! is_file( $htaccess ) || $content !== $new_content ) {
            file_put_contents( $htaccess, $new_content );
        }
        return;
    }

    if ( ! is_file( $htaccess ) ) return;
    if ( $content_without_dedo === '' ) {
        unlink( $htaccess );
    } elseif ( $content !== $content_without_dedo . "\n" ) {
        file_put_contents( $htaccess, $content_without_dedo . "\n" );
    }
}

function dedo_folder_frontend_token_box( $post_id ) {
    $post_id = absint( $post_id );
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) return '';
    if ( (int) get_post_meta( $post_id, '_dedo_folder_protect', true ) <= 0 ) return '';

    $token7 = dedo_folder_access_token_url( $post_id, 7 );
    $token365 = dedo_folder_access_token_url( $post_id, 365 );
    $uid = 'dedo-folder-token-' . $post_id;

    ob_start();
    ?>
    <div class="dedo-folder-admin-tokens" style="margin:0 0 1rem;padding:.8rem 1rem;border:1px solid #b7b7b7;background:#fff7d6">
        <strong><?php echo esc_html__( 'Admin: shareable folder access', 'delightful-downloads' ); ?></strong>
        <div style="display:grid;gap:.5rem;margin-top:.55rem">
            <label><span style="display:inline-block;min-width:70px"><strong>7 Tage</strong></span>
                <input id="<?php echo esc_attr( $uid ); ?>-7" type="text" value="<?php echo esc_attr( $token7 ); ?>" readonly style="width:min(760px,75%);cursor:pointer" onclick="this.select();">
                <button type="button" onclick="(function(){var e=document.getElementById('<?php echo esc_js( $uid ); ?>-7');e.select();if(navigator.clipboard){navigator.clipboard.writeText(e.value);}else{document.execCommand('copy');}})();">Kopieren</button>
            </label>
            <label><span style="display:inline-block;min-width:70px"><strong>365 Tage</strong></span>
                <input id="<?php echo esc_attr( $uid ); ?>-365" type="text" value="<?php echo esc_attr( $token365 ); ?>" readonly style="width:min(760px,75%);cursor:pointer" onclick="this.select();">
                <button type="button" onclick="(function(){var e=document.getElementById('<?php echo esc_js( $uid ); ?>-365');e.select();if(navigator.clipboard){navigator.clipboard.writeText(e.value);}else{document.execCommand('copy');}})();">Kopieren</button>
            </label>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
}

function dedo_render_folder_download( $post_id, $args = array() ) {
    $post_id = (int) $post_id;
    $folder = dedo_folder_path( $post_id );
    if ( ! $folder ) return '<blockquote><strong>' . esc_html__( 'Folder:', 'delightful-downloads' ) . '</strong> ' . esc_html__( 'Directory not found or not below WordPress root.', 'delightful-downloads' ) . '</blockquote>';
    dedo_folder_protection_for_post( $post_id );
    $defaults = array(
        'protect' => (int) get_post_meta( $post_id, '_dedo_folder_protect', true ),
        'showicon' => (int) get_post_meta( $post_id, '_dedo_folder_showicon', true ),
        'sort' => (string) get_post_meta( $post_id, '_dedo_folder_sort', true ) ?: 'filename',
    );
    $args = wp_parse_args( $args, $defaults );
    $access_params = dedo_folder_access_params_from_request( $post_id );
    $admin_tokens = dedo_folder_frontend_token_box( $post_id );
    if ( 2 === (int) $args['protect'] && ! dedo_folder_access_allowed( $post_id ) ) {
        return dedo_folder_styles() . $admin_tokens . '<div class="folderdir dedo-folder-download"><div class="folderdir-toolbar"><strong>' . dedo_folder_status_icon( true ) . ' ' . esc_html__( 'Protected folder', 'delightful-downloads' ) . '</strong><p>' . esc_html__( 'A valid access token is required to view or download files from this folder.', 'delightful-downloads' ) . '</p></div></div>';
    }
    $files = dedo_folder_download_scan( $post_id );
    $sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : sanitize_key( $args['sort'] );
    $search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
    dedo_folder_sort( $files, $sort );
    $desc = dedo_folder_description_map( $folder );
    if ( '' !== $search ) $files = array_values( array_filter( $files, fn( $f ) => false !== stripos( $f['name'] . ' ' . ( $desc[$f['name']] ?? '' ), $search ) ) );
    $total = count( $files ); $per_page = '' !== $search ? 1000 : 25;
    $pages = max( 1, (int) ceil( $total / $per_page ) );
    $page = min( $pages, max( 1, isset( $_GET['seite'] ) ? absint( $_GET['seite'] ) : 1 ) );
    $shown = array_slice( $files, ( $page - 1 ) * $per_page, $per_page );
    $first = $total ? ( $page - 1 ) * $per_page + 1 : 0; $last = min( $page * $per_page, $total );
    $size = array_sum( array_column( $shown, 'size' ) );
    $options = array( 'filename'=>'Filename', 'filename_desc'=>'Filename (desc)', 'date'=>'Date', 'date_desc'=>'Date (desc)', 'dlds_desc'=>'Downloads', 'size'=>'Size', 'size_desc'=>'Size (desc)', 'random'=>'Random' );
    ob_start();
    echo dedo_folder_styles();
    echo $admin_tokens;
    echo '<div class="folderdir dedo-folder-download"><div class="folderdir-toolbar"><form method="get">';
    foreach ( $access_params as $key => $value ) echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
    echo '<div class="folderdir-stats">';
    echo '<span class="meta-chip">' . dedo_folder_status_icon( 2 === (int) $args['protect'], 2 === (int) $args['protect'] ? __( 'Protected - token required', 'delightful-downloads' ) : __( 'Download available', 'delightful-downloads' ) ) . '</span>';
    echo '<span>📁 ' . esc_html( "$first-$last / $total" ) . '</span><span>📄 <strong>' . absint( $total ) . '</strong></span><span>📊 <strong>' . esc_html( size_format( $size, 1 ) ) . '</strong></span></div>';
    echo '<input type="search" name="search" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'Search Downloads', 'delightful-downloads' ) . '"><select name="sort">';
    foreach ( $options as $value => $label ) echo '<option value="' . esc_attr( $value ) . '" ' . selected( $sort, $value, false ) . '>' . esc_html( $label ) . '</option>';
    echo '</select><button type="submit">⌕</button></form></div>';
    $n = ( $page - 1 ) * $per_page + 1;
    foreach ( $shown as $file ) {
        $url = $args['protect'] ? dedo_folder_secure_url( $post_id, $file['relative'] ) : home_url( '/' . ltrim( $file['relative'], '/' ) );
        $title = wp_basename( $file['name'], '.' . $file['ext'] );
        echo '<article class="penguin-post folderdir-card">';
        if ( $args['showicon'] ) echo '<div class="folderdir-iconbox"><span class="folderdir-number">' . absint( $n ) . '</span><a href="' . esc_url( $url ) . '"><i class="folderdir-fileicon" style="--folderdir-icon:' . esc_attr( dedo_folder_icon_color( $file['ext'] ) ) . '" data-ext="' . esc_attr( $file['ext'] ) . '"></i></a></div>';
        echo '<div class="folderdir-body"><div class="meta-icons"><div class="meta-icons__bar">';
        echo '<span class="meta-chip">' . dedo_folder_status_icon( 2 === (int) $args['protect'] ) . '</span>';
        echo '<span class="meta-chip">↔ ' . esc_html( size_format( $file['size'], 1 ) ) . '</span>';
        if ( $file['dlds'] ) echo '<span class="meta-chip">⬇ ' . absint( $file['dlds'] ) . '</span>';
        echo wp_kses_post( dd_shared_colordatebox( $file['ctime'], $file['mtime'], null, 1 ) );
        echo '</div></div><a class="headline folderdir-title" href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a><div class="folderdir-description">' . wp_kses_post( $desc[$file['name']] ?? '' ) . '</div></div></article>';
        $n++;
    }
    if ( '' === $search && $pages > 1 ) {
        echo '<nav class="folderdir-pagination nav-links">';
        for ( $i=1; $i <= $pages; $i++ ) echo '<a class="page-numbers' . ( $i === $page ? ' current' : '' ) . '" href="' . esc_url( add_query_arg( array_merge( array( 'seite'=>$i, 'sort'=>$sort ), $access_params ) ) ) . '">' . $i . '</a>';
        echo '</nav>';
    }
    echo '</div>';
    return ob_get_clean();
}

function dedo_folder_styles() {
    static $done = false; if ( $done ) return ''; $done = true;
    return '<style id="dedo-folder-style">.folderdir{display:block;min-width:0}.folderdir-toolbar{margin:0 0 .55rem;padding:.45rem .55rem;background:var(--pb-panel-bg,#fff);border:1px solid var(--pb-soft-border,#ddd);border-radius:12px}.folderdir-toolbar form{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap}.folderdir-stats{display:flex;gap:.65rem;flex:1 1 320px;flex-wrap:wrap}.folderdir-card{display:flex!important;gap:.7rem!important;margin:0 0 .55rem!important;padding:.65rem!important;background:var(--pb-panel-bg,#fff)!important;border:1px solid var(--pb-soft-border,#ddd)!important;border-left:3px solid var(--pengcolor,#006060)!important;border-radius:12px!important}.folderdir-iconbox{position:relative;flex:0 0 59px}.folderdir-fileicon{display:inline-block;position:relative;width:45px;height:55px;border-radius:5px 20px 5px 5px;background:var(--folderdir-icon,#7a858a);color:#fff}.folderdir-fileicon:after{content:attr(data-ext);position:absolute;left:2px;right:2px;bottom:3px;color:#fff;font-weight:700;text-align:center;text-transform:uppercase;font-size:.72rem}.folderdir-number{position:absolute;z-index:2;left:8px;top:5px;background:#fffd;border-radius:999px;padding:0 .25rem;font-size:.75rem;font-weight:700}.folderdir-body{flex:1;min-width:0}.folderdir-title{display:block;font-weight:700;overflow-wrap:anywhere}.folderdir-description{overflow-wrap:anywhere}.folderdir-pagination{display:flex;justify-content:center;gap:.3rem;flex-wrap:wrap;margin-top:.8rem}.dedo-folder-status-icon{display:inline-flex;align-items:center;vertical-align:-.15em;color:currentColor}.dedo-folder-status-icon svg{display:block}</style>';
}

function dedo_folder_source_meta_box() {
    add_meta_box( 'dedo_source', __( 'Download source', 'delightful-downloads' ), 'dedo_folder_source_meta_box_html', 'dedo_download', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'dedo_folder_source_meta_box', 5 );

function dedo_folder_source_meta_box_html( $post ) {
    $type = get_post_meta( $post->ID, '_dedo_source_type', true ) ?: 'file';
    $path = get_post_meta( $post->ID, '_dedo_folder_path', true );
    $protect = (int) get_post_meta( $post->ID, '_dedo_folder_protect', true );
    $showicon = get_post_meta( $post->ID, '_dedo_folder_showicon', true ); if ( '' === $showicon ) $showicon = 1;
    $sort = get_post_meta( $post->ID, '_dedo_folder_sort', true ) ?: 'filename';
    wp_nonce_field( 'dedo_folder_source_save', 'dedo_folder_source_nonce' );
    echo '<p><label><input type="radio" name="dedo_source_type" value="file" ' . checked( $type, 'file', false ) . '> ' . esc_html__( 'File', 'delightful-downloads' ) . '</label> &nbsp; <label><input type="radio" name="dedo_source_type" value="folder" ' . checked( $type, 'folder', false ) . '> ' . esc_html__( 'Folder', 'delightful-downloads' ) . '</label></p>';
    $folders = dedo_folder_upload_subdirectories();
    echo '<div id="dedo-folder-options"><p><label for="dedo_folder_path"><strong>' . esc_html__( 'Folder below uploads', 'delightful-downloads' ) . '</strong></label><br>';
    echo '<select class="widefat" id="dedo_folder_path" name="dedo_folder_path">';
    echo '<option value="">' . esc_html__( 'Select a folder…', 'delightful-downloads' ) . '</option>';
    $known_current = false;
    foreach ( $folders as $folder_value => $folder_label ) {
        if ( $path === $folder_value ) $known_current = true;
        echo '<option value="' . esc_attr( $folder_value ) . '" ' . selected( $path, $folder_value, false ) . '>' . esc_html( $folder_label ) . '</option>';
    }
    if ( $path && ! $known_current ) {
        echo '<option value="' . esc_attr( $path ) . '" selected>' . esc_html__( 'Previous selection (currently unavailable): ', 'delightful-downloads' ) . esc_html( $path ) . '</option>';
    }
    echo '</select></p>';
    echo '<p><label>' . esc_html__( 'Folder Protection', 'delightful-downloads' ) . ' <select name="dedo_folder_protect"><option value="0" ' . selected($protect,0,false) . '>0 - public/direct</option><option value="1" ' . selected($protect,1,false) . '>1 - protected path, download without token</option><option value="2" ' . selected($protect,2,false) . '>2 - token required</option></select></label> &nbsp; ';
    echo '<label><input type="checkbox" name="dedo_folder_showicon" value="1" ' . checked((int)$showicon,1,false) . '> Icons</label> &nbsp; ';
    echo '<label>Sort <select name="dedo_folder_sort">'; foreach(array('filename','filename_desc','date','date_desc','dlds_desc','size','size_desc','random') as $v) echo '<option value="'.$v.'" '.selected($sort,$v,false).'>'.$v.'</option>'; echo '</select></label></p>';
    if ( $post->ID && 'publish' === get_post_status( $post->ID ) ) {
        $token7 = dedo_folder_access_token_url( $post->ID, 7 );
        $token365 = dedo_folder_access_token_url( $post->ID, 365 );
        echo '<div class="dedo-folder-token-box" style="margin:10px 0;padding:10px;border:1px solid #ccd0d4;background:#fff"><strong>' . esc_html__( 'Shareable folder access tokens', 'delightful-downloads' ) . '</strong>';
        echo '<p class="description">' . esc_html__( 'Level 1 can be downloaded without a token; these links are still available for sharing. Level 2 requires a valid token for visitors.', 'delightful-downloads' ) . '</p>';
        echo '<p><label><strong>7 Tage</strong><br><input class="widefat copy-to-clipboard" type="text" value="' . esc_attr( $token7 ) . '" readonly></label></p>';
        echo '<p><label><strong>365 Tage</strong><br><input class="widefat copy-to-clipboard" type="text" value="' . esc_attr( $token365 ) . '" readonly></label></p></div>';
    }
    echo '<p class="description">' . esc_html__( 'Only subfolders of the WordPress uploads directory are available. descriptions.txt in the selected folder can contain filename,description.', 'delightful-downloads' ) . '</p></div>';
    echo '<script>document.addEventListener("DOMContentLoaded",function(){const radios=document.querySelectorAll("input[name=dedo_source_type]"),opts=document.getElementById("dedo-folder-options"),filebox=document.getElementById("dedo_download");function sync(){const f=document.querySelector("input[name=dedo_source_type]:checked")?.value==="folder";opts.style.display=f?"block":"none";if(filebox)filebox.style.display=f?"none":"block";}radios.forEach(r=>r.addEventListener("change",sync));sync();});</script>';
}

function dedo_folder_source_save( $post_id ) {
    if ( get_post_type( $post_id ) !== 'dedo_download' || ! current_user_can( 'edit_post', $post_id ) || ! isset($_POST['dedo_folder_source_nonce']) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['dedo_folder_source_nonce'])), 'dedo_folder_source_save' ) ) return;
    $type = isset($_POST['dedo_source_type']) && 'folder' === $_POST['dedo_source_type'] ? 'folder' : 'file';
    update_post_meta( $post_id, '_dedo_source_type', $type );
    $folder_path = sanitize_text_field( wp_unslash( $_POST['dedo_folder_path'] ?? '' ) );
    $allowed_folders = dedo_folder_upload_subdirectories();
    if ( 'folder' === $type && ! isset( $allowed_folders[ $folder_path ] ) ) $folder_path = '';
    update_post_meta( $post_id, '_dedo_folder_path', $folder_path );
    update_post_meta( $post_id, '_dedo_folder_protect', min(2, absint($_POST['dedo_folder_protect'] ?? 0)) );
    update_post_meta( $post_id, '_dedo_folder_showicon', isset($_POST['dedo_folder_showicon']) ? 1 : 0 );
    update_post_meta( $post_id, '_dedo_folder_sort', sanitize_key($_POST['dedo_folder_sort'] ?? 'filename') );
    if ( 'folder' === $type ) {
        update_post_meta( $post_id, '_dedo_file_url', 'folder://' . ltrim( $folder_path, '/' ) );
        $files = dedo_folder_download_scan( $post_id );
        update_post_meta( $post_id, '_dedo_file_size', array_sum( array_column( $files, 'size' ) ) );
        dedo_folder_protection_for_post( $post_id );
    }
}
add_action( 'save_post_dedo_download', 'dedo_folder_source_save', 20 );
