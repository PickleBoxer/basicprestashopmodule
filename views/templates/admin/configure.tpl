{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Basic PrestaShop Module' mod='basicprestashopmodule'}</h3>
	<p>
		<strong>{l s='Here is my new generic module!' mod='basicprestashopmodule'}</strong><br />
		{l s='Thanks to PrestaShop, now I have a great module.' mod='basicprestashopmodule'}<br />
		{l s='I can configure it using the following configuration form.' mod='basicprestashopmodule'}
	</p>
	<br />
	<p>
		{l s='This module will boost your sales!' mod='basicprestashopmodule'}
	</p>
</div>

<div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='Documentation' mod='basicprestashopmodule'}</h3>
	<p>
		&raquo; {l s='You can get a PDF documentation to configure this module' mod='basicprestashopmodule'} :
	<ul>
		<li><a href="#" target="_blank">{l s='English' mod='basicprestashopmodule'}</a></li>
		<li><a href="#" target="_blank">{l s='French' mod='basicprestashopmodule'}</a></li>
	</ul>
	</p>
</div>

<div class="alert"></div>

<script>
	$.ajax({
		url: '{$controller_link}',
		type: 'POST',
		data: {
			ajax: true,
			action: 'FetchUpdates'
		},
		success: function(response) {
			// Parse the response as JSON
			response = JSON.parse(response);
			// Handle the updates here
			console.log(response);
			if (response.status === 'success') {
				if (response.updates) {
					// Show Bootstrap notification for available updates
					$('.alert').addClass('alert-success').html(response.message + '<hr>Version: ' + response
							.updates + '<br><button class="btn btn-primary" id="update-btn">Update</button>')
						.show();
				} else {
					// Show Bootstrap notification for no updates available
					$('.alert').addClass('alert-info').html(response.message).show();
				}
			} else {
				// Show Bootstrap notification for error
				$('.alert').addClass('alert-danger').html(response.message).show();
			}
		},
		error: function() {
			// Show Bootstrap notification for error
			$('.alert').addClass('alert-danger').html('Error fetching updates').show();
		}
	});

	$(document).on('click', '#update-btn', function() {
		$.ajax({
			url: '{$controller_link}',
			type: 'POST',
			data: {
				ajax: true,
				action: 'InstallUpdates'
			},
			beforeSend: function() {
				// Show the load indicator
				$('.alert').addClass('alert-info').html('Updating...').show();
			},
			success: function(response) {
				$('.alert').removeClass('alert-info');
				// Parse the response as JSON
				response = JSON.parse(response);
				// Handle the updates here
				console.log(response);
				if (response.status === 'success') {
					// Show Bootstrap notification for successful update
					$('.alert').addClass('alert-success').html(response.message).show();
					// call ajax to upgrade-ajax.php file
					$.ajax({
						url: '{$upgrade_ajax_link}',
						type: 'POST',
						success: function(response) {
							// Parse the response as JSON
							response = JSON.parse(response);
							// Handle the updates here
							console.log(response);
							if (response.status === 'success') {
								// Show Bootstrap notification for successful update
								$('.alert').addClass('alert-success').html(response
									.message).show();
							} else {
								// Show Bootstrap notification for error
								$('.alert').addClass('alert-danger').html(response
										.message + '<hr>' + response.errors.join('<br>'))
									.show();
							}
						},
						error: function() {
							// Show Bootstrap notification for error
							$('.alert').addClass('alert-danger').html(
								'Error doing updates').show();
						}
					});
				} else {
					// Show Bootstrap notification for error
					$('.alert').addClass('alert-danger').html(response.message).show();
				}
			},
			error: function() {
				// Show Bootstrap notification for error
				$('.alert').addClass('alert-danger').html('Error doing updates').show();
			}
		});
	});
</script>