<input type="text" name="delightful-downloads[<?php echo $key; ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
<?php if ( $active ) : ?>
	<button type="submit" class="dedo-deactivate-license button-secondary"><?php _e( 'Deactivate', 'delightful-downloads' ); ?></button>
	<p class="description"><?php printf( __( 'Your license will expire on %s.', 'delightful-downloads' ), date( 'F jS Y', strtotime( $status->expires ) ) ); ?></p>
<?php endif; ?>