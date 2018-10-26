<?php
$my_saved_attachment_post_id = get_option( 'media_selector_attachment_id', 0 );
?>
<script type="text/javascript">
jQuery(document).ready(function ($) {
	var rans = [];

	$('#add-row').on('click', function () {
		var ran = Math.floor(Math.random() * 100);
		rans.push("Woo"+ran);
		var img = document.getElementById("image-preview");
		for(var i = 0, j = rans.length; i < j; i++) {
			if( img.classList.length == 0) {
			// if( ! img.classList.contains(rans[i])) {
			document.getElementById("image-preview").className = "Woo" + ran
				break;
			}
		}
		// document.getElementById("image-preview").className = "Woo" + ran;
		// delete ran;
		var row = $('.empty-row.screen-reader-text').clone(true);
		row.removeClass('empty-row screen-reader-text');
		row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
		return false;
	});

	$('.remove-row').on('click', function () {
		$(this).parents('tr').remove();
		return false;
	});

	// Uploading files
	var file_frame;
	var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
	var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this
	jQuery('#upload_image_button').on('click', function( event ){
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			// Set the post ID to what we want
			file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
			// Open frame
			file_frame.open();
			return;
		} else {
			// Set the wp.media post id so the uploader grabs the ID we want when initialised
			wp.media.model.settings.post.id = set_to_post_id;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select a image to upload',
			button: {
				text: 'Use this image',
			},
			multiple: false	// Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
			$( '#image_attachment_id' ).val( attachment.id );
			// Restore the main post ID
			wp.media.model.settings.post.id = wp_media_post_id;
		});
		// Finally, open the modal
		file_frame.open();
	});
	// Restore the main ID when the add media button is pressed
	jQuery( 'a.add_media' ).on( 'click', function() {
		wp.media.model.settings.post.id = wp_media_post_id;
	});
});
</script>

<table id="repeatable-fieldset-one" width="100%">
	<tbody>
		<?php
		if ( $rop_custom_messages_group ) {
			$i = 1;
			foreach ( $rop_custom_messages_group as $field ) {
				echo '
				<tr>
				<td width="15%">
				<b>' . Rop_I18n::get_labels( 'post_editor.message_no' ) . $i ++ . '</b><br/>
				<small><i>' . Rop_I18n::get_labels( 'post_editor.random_message_info' ) . '</i></small>
				</td>
				<td width="70%">
				<textarea placeholder="Description" cols="55" rows="5" name="rop_custom_description[]" style="width: 100%;">' . ( ( $field['rop_custom_description'] != '' ) ? esc_attr( $field['rop_custom_description'] ) : '' ) . '</textarea></td>
				<td width="15%"><a class="button remove-row" href="#1">' . Rop_I18n::get_labels( 'post_editor.remove_message' ) . '</a></td>
				</tr>
				';
			}
		} else {
			echo '
			<tr>
			<td width="15%">
			<b>' . Rop_I18n::get_labels( 'post_editor.message_no' ) . '</b><br/>
			<small><i>' . Rop_I18n::get_labels( 'post_editor.random_message_info' ) . '</i></small>
			</td>
			<td width="70%">
			<textarea  placeholder="' . Rop_Pro_I18n::get_labels( 'magic_tags.example' ) . '" name="rop_custom_description[]" cols="55" rows="5" style="width: 100%;"></textarea>
			</td>
			<td width="15%"></td>

			</tr>
			';
		}
		?>
		<tr class="empty-row screen-reader-text">
			<td width="15%">
				<b><?php echo Rop_I18n::get_labels( 'post_editor.message_no' ); ?></b><br/>
				<small>
					<i><?php echo Rop_I18n::get_labels( 'post_editor.random_message_info' ); ?></i>
				</small>
				<td width="70%">
					<textarea placeholder="<?php echo Rop_Pro_I18n::get_labels( 'magic_tags.example' ); ?>" cols="55" rows="5" name="rop_custom_description[]"
						style="width: 100%;"></textarea>
					</td>
					<td width="15%"><a class="button remove-row"
						href="#"><?php echo Rop_I18n::get_labels( 'post_editor.remove_message' ); ?></a></td>
						<td>
							<?php wp_enqueue_media();	?>
							<div class='image-preview-wrapper'>
								<img id='image-preview' src='' width='100' height='100' style='max-height: 100px; width: 100px;'>
							</div>
							<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image!' ); ?>" />
							<input type='hidden' name='image_attachment_id' id='image_attachment_id' value=''>
						</td>
					</tr>
				</tbody>
			</table>
			<p><a id="add-row" class="button" href="#"><?php echo Rop_I18n::get_labels( 'post_editor.add_message' ); ?></a></p>
