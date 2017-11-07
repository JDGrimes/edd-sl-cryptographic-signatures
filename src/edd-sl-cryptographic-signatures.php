<?php

/**
 * Plugin Name: EDD SL Cryptographic Signatures
 * Author:      J.D. Grimes
 * Author URI:  https://codesymphony.co/
 * Plugin URI:  https://github.com/JDGrimes/edd-sl-cryptographic-signatures
 * Version:     1.0.0
 * License:     GPLv2+
 * Description: Adds support for digitally signed packages to the Easy Digital Downloads Software Licenses extension.
 * Text Domain: edd-sl-cryptographic-signatures
 * Domain Path: /languages
 *
 * ---------------------------------------------------------------------------------|
 * Copyright 2017  J.D. Grimes  (email : jdg@codesymphony.co)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or later, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * ---------------------------------------------------------------------------------|
 *
 * @package edd-sl-cryptographic-signatures
 * @version 1.0.0
 * @author  J.D. Grimes <jdg@codesymphony.co>
 * @license GPLv2+
 */

/**
 * Adds the Ed25519 Signature field to the file table rows.
 *
 * @since 1.0.0
 *
 * @WordPress\action edd_download_file_table_row
 *
 * @param int $post_id The ID of the download.
 * @param int $key     The key for the current file.
 */
function edd_sl_cryptographic_signatures_download_file_table_row( $post_id, $key ) {

	$ed25519_signature = null;

	$files = edd_get_download_files( $post_id );

	if ( isset( $files[ $key ]['ed25519_signature'] ) ) {
		$ed25519_signature = $files[ $key ]['ed25519_signature'];
	}

	?>

	<div class="edd-sl-cryptographic-signature">
		<span class="edd-repeatable-row-setting-label"><?php _e( 'Ed25519 Signature', 'easy-digital-downloads' ); ?></span>
		<?php

		echo EDD()->html->text(
			array(
				'name'        => 'edd_download_files[' . $key . '][ed25519_signature]',
				'value'       => $ed25519_signature,
				'placeholder' => __( 'Ed25519 Signature', 'easy-digital-downloads' ),
				'class'       => 'edd_repeatable_ed25519_signature_field large-text'
			)
		);

		?>
	</div>

	<?php
}
add_action( 'edd_download_file_table_row', 'edd_sl_cryptographic_signatures_download_file_table_row', 10, 2 );

/**
 * Adds the package signature to the license response.
 *
 * @since 1.0.0
 *
 * @WordPress\filter edd_sl_license_response
 *
 * @param array   $response      The response data.
 * @param WP_Post $download      The download the response is for.
 * @param bool    $download_beta Whether a beta version is being supplied.
 *
 * @return array The response, with the package signature added.
 */
function edd_sl_cryptographic_signatures_license_response( $response, $download, $download_beta = false ) {

	$response['ed25519_signature'] = edd_sl_cryptographic_signatures_get_for_download(
		$download->ID
		, $download_beta
	);

	if ( $download_beta ) {
		$response['stable_ed25519_signature'] = edd_sl_cryptographic_signatures_get_for_download(
			$download->ID
		);
	}

	return $response;
}
add_filter( 'edd_sl_license_response', 'edd_sl_cryptographic_signatures_license_response', 10, 3 );

/**
 * Gets the Ed25519 cryptographic signature for the latest version of a download.
 *
 * @since 1.0.0
 *
 * @param int  $download_id   The ID of the download.
 * @param bool $download_beta Whether to get the beta version's signature. Default is
 *                            false.
 *
 * @return string The Ed25519 cryptographic signature for the download.
 */
function edd_sl_cryptographic_signatures_get_for_download(
	$download_id,
	$download_beta = false
) {

	if ( $download_beta ) {
		$file_key  = get_post_meta( $download_id, '_edd_sl_beta_upgrade_file_key', true );
		$all_files = get_post_meta( $download_id, '_edd_sl_beta_files', true );
	} else {
		$file_key  = get_post_meta( $download_id, '_edd_sl_upgrade_file_key', true );
		$all_files = get_post_meta( $download_id, 'edd_download_files', true );
	}

	if ( ! isset( $all_files[ $file_key ]['ed25519_signature'] ) ) {
		return '';
	}

	return $all_files[ $file_key ]['ed25519_signature'];
}

// EOF
