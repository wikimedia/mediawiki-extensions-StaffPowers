<?php
/**
 * Applies staff powers, like unblockableness, superhuman strength and
 * general awesomeness to select users.
 *
 * @file
 * @ingroup Extensions
 * @version 1.3
 * @date 20 November 2015
 * @author Łukasz Garczewski <tor@wikia-inc.com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 3.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:StaffPowers Documentation
 */

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'StaffPowers',
	'version' => '1.3',
	'author' => array( 'Łukasz Garczewski', 'Jack Phoenix' ),
	'description' => 'Applies staff powers, like unblockableness, superhuman strength and general awesomeness to [[Special:ListUsers/staff|select users]]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:StaffPowers',
);

$wgMessagesDirs['StaffPowers'] = __DIR__ . '/i18n';

$wgAutoloadClasses['StaffPowers'] = __DIR__ . '/StaffPowers.class.php';

// Power: unblockableness
$wgHooks['BlockIp'][] = 'StaffPowers::makeUnblockable';

$wgAvailableRights[] = 'unblockable';
$wgGroupPermissions['staff']['unblockable'] = true;