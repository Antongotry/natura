<?php

function natura_is_front_page(): bool {
	return is_front_page() || (is_home() && 'page' === get_option('show_on_front'));
}








