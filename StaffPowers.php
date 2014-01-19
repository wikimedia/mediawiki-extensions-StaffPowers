<?php
/**
 * Applies staff powers, like unblockableness, superhuman strength and
 * general awesomeness to select users.
 *
 * @file
 * @ingroup Extensions
 * @version 1.1
 * @date 19 January 2014
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
	'version' => '1.1',
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
 * @param Block $block The Block object about to be saved
 * @param User $user The user _doing_ the block (not the one being blocked)
 * @param array $reason Custom reason as to why blocking isn't possible
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
	$userIsStaff = in_array( 'staff', $blockedUser->getEffectiveGroups() );
	$userIsSteward = in_array( 'steward', $blockedUser->getEffectiveGroups() );
	$blockerIsStaff = in_array( 'staff', $user->getEffectiveGroups() );

	// Don't allow staff to be blocked in any circumstances
	if ( $userIsStaff ) {
		$reason = array( 'staffpowers-ipblock-abort' );
	} elseif ( $userIsSteward && !$blockerIsStaff ) {
		// and also don't allow stewards to be blocked by non-staff, as per IRC
		// discussion on 19 January 2014
		$reason = array( 'staffpowers-steward-block-abort' );
	}

	return false;
}