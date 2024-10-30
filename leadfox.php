<?php
/**
 * Plugin Name: Leadfox
 * Version: 2.1.8
 * Author: Leadfox
 * Description: Leadfox converts visitors into ripe leads and paying customers.
 */

// Security check
defined("ABSPATH") or die("No script kiddies please!");

add_action("plugins_loaded", "leadfox_plugins_loaded");

// Styles
function leadfox_enqueue_styles() {
	wp_enqueue_style("leadfox", plugins_url("css/leadfox.css", __FILE__));
}
add_action("wp_enqueue_scripts", "leadfox_enqueue_styles");

function leadfox_plugins_loaded() {
	// Globals
	global $leadfox_jwt;
	$leadfox_jwt = null;

	global $leadfox_notices;
	$leadfox_notices = array();

	// Defines
	define("LF_GET", "GET");
	define("LF_POST", "POST");
	define("LF_PUT", "PUT");
	define("LF_DELETE", "DELETE");

	// Main
	if (leadfox_post("submit") !== null) leadfox_update_options();
	if (leadfox_post("sync") !== null) leadfox_sync_contacts();
}

/**
 * Plugin installation
 */
function lf_install() {
}
register_activation_hook(__FILE__, "lf_install");

/**
 * Uninstall leadfox plugin
 */
function lf_uninstall() {
}
register_deactivation_hook(__FILE__, "lf_uninstall");

/**
 * Register settings page
 */
function leadfox_admin_init() {
	register_setting("leadfox", "apikey");
	register_setting("leadfox", "secret");
	register_setting("leadfox", "list");
}
add_action("admin_init", "leadfox_admin_init");

/**
 * Get a POST variable
 * @param {string} var - Post variable name
 * @return {string} - Value or null
 */
function leadfox_post($var) {
	return isset($_POST[$var]) ? $_POST[$var] : null;
}

/**
 * Get the URL of an image for this plugin
 * @param {string} s - Image file name
 * @return {string} - Url of the image
 */
function leadfox_img($s) {
	return plugins_url("images/".$s, __FILE__);
}

/**
 * Get options or post data
 * @return {array} - Options
 */
function leadfox_options() {
	// Get post or configuration values
	return array(
		"apikey" => get_option("leadfox_apikey"),
		"secret" => get_option("leadfox_secret"),
		"list" => get_option("leadfox_list")
	);
}

/**
 * Update options
 */
function leadfox_update_options() {
	$apikey = leadfox_post("apikey");
	if (!empty($apikey)) update_option("leadfox_apikey", $apikey, true);

	$secret = leadfox_post("secret");
	if (!empty($secret)) update_option("leadfox_secret", $secret, true);

	$list = leadfox_post("list");
	if (!empty($list)) update_option("leadfox_list", $list, true);

	leadfox_notice(__("Your settings were saved correctly."), "success");
}

/**
 * Check if a user as one of the roles passed
 * @param {array} info - User roles
 * @param {array} roles - Roles to find
 * @return {boolean} - If any of the roles were found for user
 */
function leadfox_hasrole($info, $roles) {
	foreach ($roles as $role) if (in_array($role, $info)) return true;
	return false;
}

/**
 * Get Rest API auth
 */
function leadfox_rest_auth() {
	$options = leadfox_options();

	$auth = leadfox_rest(LF_POST, "auth", array(
		"apikey" => $options["apikey"],
		"secret" => $options["secret"]
	), true);

	if ($auth["status"] == 200) return $auth["body"];
	else leadfox_notice(__("You must provide a valid API key and secret."), "error");
}

/**
 * Make a call to the Leadfox Rest API
 * @param {string} method - Method to use
 * @param {string} url - The relative endpoint url
 * @param {array} body - The request's body
 * @return {array} - The response array
 */
function leadfox_rest($method, $url, $body = null, $isauth = false) {
	global $leadfox_jwt;

	if (!$leadfox_jwt && !$isauth) $leadfox_jwt = leadfox_rest_auth();

	$headers = array(
		"Content-Type: application/json",
	);

	if ($leadfox_jwt) {
		array_push($headers, "Authorization: JWT ".$leadfox_jwt["jwt"]);

		if (isset($leadfox_jwt["clientid"])) {
			if (strpos($url, "?") === false) $url .= "?";
			else $url .= "&";

			$url .= "clientid=".$leadfox_jwt["clientid"];
		}
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_URL, "https://rest.leadfox.co/v1/".$url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if (is_array($body)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

	$response = curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);

	curl_close ($ch);

	$ret = array(
		"body" => json_decode($response, true),
		"status" => $status,
		"error" => $error
	);

	if (!$isauth && $status >= 400) {
		leadfox_notice(__("There was a problem communicating with Leadfox")." ($status).", "error");
		error_log("Leadfox Rest API: ".json_encode($ret));
	}

	return $ret;
}

/**
 * The leadfox admin menu hook
 */
function leadfox_admin_menu() {
	add_menu_page(
		"Leadfox",
		"Leadfox",
		"manage_options",
		"leadfox-menu",
		"leadfox_menu_output",
		leadfox_img("icon.png")
	);
}
add_action("admin_menu", "leadfox_admin_menu");

/**
 * Register a notice
 * @param {string} class - Message class
 * @param {string} msg - Message
 */
function leadfox_notice($msg, $class = "info") {
	global $leadfox_notices;
	array_push($leadfox_notices, array("msg" => $msg, "class" => $class));
}

/**
 * Leadfox notices
 */
function leadfox_admin_notices() {
	global $leadfox_notices;
	$html = "";

	foreach ($leadfox_notices as $notice) $html .= '<div class="notice notice-'.esc_attr($notice["class"]).' is-dismissible"><p>'.esc_html($notice["msg"]).'</p></div>';

	return $html;
}

/**
 * Output the page
 * @param {string} buffer - Output buffer
 */
function leadfox_output($buffer) {
	return leadfox_admin_notices().$buffer;
}

/**
 * The plugin's page hook
 */
function leadfox_menu_output() {
	$options = leadfox_options();
	$lists = leadfox_rest(LF_GET, "list?sort=name");

	ob_start("leadfox_output");
?>
	<div class="leadfox-plugin">
		<form name="leadfox-options" method="post">
			<img src="<?php echo leadfox_img("logo.png") ?>" alt="Leadfox">
			<h1>Wordpress Integration</h1>

			<p><?php _e("Enter your API key and secret to integrate the LeadFox tracking codes.") ?><br>
			<?php _e("These can be found under the API section of the Manage menu in your Leadfox account.") ?></p>
			<label for="lf-key"><?php _e("API key") ?></label>
			<div>
				<input id="lf-key" type="text" name="apikey" value="<?php echo $options["apikey"] ?>">
			</div>

			<label for="lf-secret"><?php _e("Secret") ?></label>
			<div>
				<input id="lf-secret" type="text" name="secret" value="<?php echo $options["secret"] ?>">
			</div>

			<label for="lf-list"><?php _e("List for new contacts") ?></label>
			<div>
				<select id="lf-list" name="list">
					<option value=""><?php _e("No list") ?></option>

					<?php if ($lists["body"]) foreach ($lists["body"] as $list): ?>
						<option value="<?php echo $list["_id"] ?>" <?php if ($list["_id"] == $options["list"]) echo "selected"?>><?php echo $list["name"] ?></option>
					<?php endforeach ?>
				</select>
			</div>

			<div>
				<button type="submit" name="submit"><?php _e("Submit") ?></button>
			</div>

			<hr>
			<label><?php _e("Sync all contacts now") ?></label>
			<div>
				<button type="submit" name="sync"><?php _e("Sync") ?></button>
			</div>
		</form>
	</div>
<?php
	ob_end_flush();
}

/**
 * New user registration hook
 * @param {integer} uid - User ID
 * @return {boolean} - Contact updated?
 */
function leadfox_user_register($uid) {
	$options = leadfox_options();
	$info = get_userdata($uid);

	if (leadfox_hasrole($info->roles, array("subscriber", "customer")) && $options["list"]) {
		// Send new contact
		$body = array(
			"name" => $info->first_name." ".$info->last_name,
			"email" => $info->user_email,
			"properties" => array(
				"firstname" => $info->first_name,
				"lastname" => $info->last_name,
				"phone" => get_user_meta($uid, "billing_phone", true)
			)
		);

		$contact = leadfox_rest(LF_POST, "contact/upsert", $body);

		// Add contact to list
		$url = "list/".$options["list"]."/contact/".$contact["body"]["_id"];
		$list = leadfox_rest(LF_POST, $url);

		return true;
	}

	return false;
}
add_action("user_register", "leadfox_user_register");

/**
 * Sync all contacts
 */
function leadfox_sync_contacts() {
	$count = 0;

	$users = get_users(array(
		"role__in" => array("subscriber", "customer"),
		"orderby" => "id",
		"fields" => array("id")
	));

	foreach ($users as $user) if (leadfox_user_register($user->id)) $count++;
	leadfox_notice($count." ".__("contact(s) updated"), "success");
}

/**
 * Add the tracking code to all pages
 */
function leadfox_footer() {
	global $wp;
	$url = home_url(add_query_arg(array(), $wp->request));
	$options = leadfox_options();
?>
	<?php if (strpos($url, "order-received") !== false): ?>
		<lf-lifecycle data-lifecycle="5" />
	<?php endif ?>

	<!-- This site is trusting LeadFox to convert visitors into customers - https://leadfox.co -->
	<script async src="//app.leadfox.co/js/api/leadfox.js" data-key="<?php echo $options["apikey"] ?>"></script>
	<!-- LeadFox -->
<?php
}
add_action("wp_footer", "leadfox_footer");