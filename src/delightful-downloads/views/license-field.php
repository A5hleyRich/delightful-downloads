<input type="text" name="delightful-downloads[<?php echo $key; ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
<?php if ( isset( $status->success ) && $status->success ) : ?>
	<p class="description"><?php printf( __( 'Your license will expire on %s.', 'delightful-downloads' ), date( 'F jS Y', strtotime( $status->expires ) ) ); ?></p>
<?php endif; ?>