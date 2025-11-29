<?php
/**
 * Applies staff powers, like unblockableness, superhuman strength and
 * general awesomeness to select users.
 *
 * @file
 * @ingroup Extensions
 * @version 1.5
 * @date 24 June 2025
 * @author Łukasz Garczewski <tor@wikia-inc.com>
 * @author Jack Phoenix
 * @author Mainframe98 <mainframe98@outlook.com>
 * @license GPL-3.0-or-later
 * @link https://www.mediawiki.org/wiki/Extension:StaffPowers Documentation
 */

namespace MediaWiki\Extension\StaffPowers;

use MediaWiki\Hook\BlockIpHook;

class Hooks implements BlockIpHook {
	/** @var MediaWiki\User\UserFactory */
	private $userFactory;
	/** @var MediaWiki\User\UserNameUtils */
	private $userNameUtils;
	/** @var MediaWiki\User\UserGroupManager */
	private $userGroupManager;
	/** @var MediaWiki\HookContainer\HookContainer */
	private $hookContainer;

	/**
	 * @param MediaWiki\User\UserFactory $userFactory
	 * @param MediaWiki\User\UserNameUtils $userNameUtils
	 * @param MediaWiki\User\UserGroupManager $userGroupManager
	 * @param MediaWiki\HookContainer\HookContainer $hookContainer
	 */
	public function __construct( $userFactory, $userNameUtils, $userGroupManager, $hookContainer ) {
		$this->userFactory = $userFactory;
		$this->userNameUtils = $userNameUtils;
		$this->userGroupManager = $userGroupManager;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * Makes users with the unblockable right (staff members) completely unblockable and stewards
	 * unblockable by non-staff users.
	 *
	 * @param Block $block The Block object about to be saved
	 * @param User $user The user _doing_ the block (not the one being blocked)
	 * @param array &$reason Custom reason as to why blocking isn't possible
	 * @return bool
	 */
	public function onBlockIp( $block, $user, &$reason ) {
		global $wgStaffPowersStewardGroupName, $wgStaffPowersShoutWikiMessages;

		$blockedUser = $this->userFactory->newFromName( $block->getRedactedName() );
		if ( !$blockedUser ) {
			return true;
		}

		if ( empty( $blockedUser ) || $this->userNameUtils->isIP( $blockedUser ) ) {
			return true;
		}

		$userIsSteward = false;
		if ( !empty( $wgStaffPowersStewardGroupName ) ) {
			$userIsSteward = in_array(
				$wgStaffPowersStewardGroupName, $this->userGroupManager->getUserEffectiveGroups( $blockedUser )
			);
		}

		$userIsUnblockable = $blockedUser->isAllowed( 'unblockable' );
		if ( !$userIsUnblockable && !$userIsSteward ) {
			return true;
		}

		// This exists for interoperability purposes with Wikia's StaffLog extension
		$this->hookContainer->run( 'BlockIpStaffPowersCancel', [ $block, $user ] );

		// Display a custom reason as to why blocking the specified user isn't
		// possible instead of the totally unhelpful, default core message

		// Don't allow users with the unblockable right (staff) to be blocked in any circumstances
		if ( $userIsUnblockable ) {
			$reason = [ 'staffpowers-ipblock-abort' ];
		} elseif ( $userIsSteward ) {
			if ( $user->isAllowed( 'unblockable' ) ) {
				// This is a possible scenario - staff are allowed to block stewards.
				// We need to address this situation 'cause this function returns false
				// by default, so w/o this elseif loop, staff trying to block a steward
				// will bump into the default core hookaborted message and the block
				// will fail.
				return true;
			} else {
				// and also don't allow stewards to be blocked by non-staff, as per IRC
				// discussion on 19 January 2014
				$reason = [ 'staffpowers-steward-block-abort' ];
			}
		}

		// Override the reason message when not run on ShoutWiki
		if ( !$wgStaffPowersShoutWikiMessages ) {
			$reason = [ 'staffpowers-unblockable-abort' ];
		}

		return false;
	}
}
