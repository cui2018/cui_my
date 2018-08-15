<?php
class Setting extends Controller {
	public function index() {
		$setting = Load::redis()->get('setting');
		$setting = $setting ? $setting : array();
		api_success($setting);
	}
}