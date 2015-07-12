<?php
chdir( $src_dir );
system( 'npm install' );
system( 'grunt' );
system( 'grunt translate' );