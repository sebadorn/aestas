<?php


function spawn_cron( $local_time ) {
	// TODO: spawn_cron
	return null;
}

function wp_clear_scheduled_hook( $name ) {
	// TODO: wp_clear_scheduled_hook
}

function wp_cron() {
	// TODO: wp_cron
	return null;
}

function wp_get_schedule( $hook, $args = array() ) {
	// TODO: wp_get_schedule
	return false;
}

function wp_get_schedules() {
	// TODO: wp_get_schedules
	return array();
}

/**
 * @return int Timestamp of the next scheduled event.
 */
function wp_next_scheduled( $hook, $args = array() ) {
	// TODO: wp_next_scheduled
	return 0;
}

function wp_reschedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
	// TODO: wp_reschedule_event
}

function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
	// TODO: wp_schedule_event
}

function wp_schedule_single_event( $timestamp, $hook, $args = array() ) {
	// TODO: wp_schedule_single_event
}

function wp_unschedule_event( $timestamp, $hook, $args = array() ) {
	// TODO: wp_unschedule_event
}