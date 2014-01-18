<?php
/**
 * Applies staff powers, like unblockableness, superhuman strength and
 * general awesomeness to select users.
 *
 * @file
 * @ingroup Extensions
 * @version 1.0
 * @date 18 January 2014
 * @author Łukasz Garczewski <tor@wikia-inc.com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 3.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:StaffPowers Documentation
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is not a valid entry point to MediaWiki.' );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'StaffPowers',
	'version' => '1.0',
	'author' => array( 'Łukasz Garczewski', 'Jack Phoenix' ),
	'description' => 'Applies staff powers, like unblockableness, superhuman strength and general awesomeness to [[Special:ListUsers/staff|select users]]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:StaffPowers',
);

$wgExtensionMessagesFiles['StaffPowers'] = dirname( __FILE__ ) . '/StaffPowers.i18n.php';

// Power: unblockableness
$wgHooks['BlockIp'][] = 'efPowersMakeUnblockable';

$wgAvailableRights[] = 'unblockable';
$wgGroupPermissions['staff']['unblockable'] = true;

/**
 * @param Block $block
 * @param User $user
 * @param array $reason
 * @return bool
 */
function efPowersMakeUnblockable( $block, $user, $reason ) {
	$blockedUser = User::newFromName( $block->getRedactedName() );

	if ( empty( $blockedUser ) || !$blockedUser->isAllowed( 'unblockable' ) ) {
		return true;
	}

	// This exists for interoperability purposes with Wikia's StaffLog extension
	wfRunHooks( 'BlockIpStaffPowersCancel', array( $block, $user ) );

	// Display a custom reason as to why blocking the specified user isn't
	// possible instead of the totally unhelpful, default core message
	$reason = array( 'staffpowers-ipblock-abort' );

	return false;
}