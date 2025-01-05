<?php
/*********************************************************************************
 *
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 *
 ********************************************************************************/

use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * @package WebAuthn
 */
class TTPublicKeyCredentialSourceRepository implements PublicKeyCredentialSourceRepository {

	public function findOneByCredentialId( string $publicKeyCredentialId ): ?PublicKeyCredentialSource {
		$uilf = TTnew( 'UserIdentificationListFactory' ); /** @var UserIdentificationListFactory $uilf */
		$uilf->getByTypeIdAndValue( 50, base64_encode( $publicKeyCredentialId ) );
		if ( $uilf->getRecordCount() > 0 ) {
			$ui_obj = $uilf->getCurrent(); /** @var UserIdentificationFactory $ui_obj */
			$public_key_credential_source = Webauthn\PublicKeyCredentialSource::createFromArray( json_decode( $ui_obj->getExtraValue(), true ) );

			return $public_key_credential_source;
		}

		return null;
	}

	public function findAllForUserEntity( PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity ): array {
		global $current_user;

		$public_key_credential_source_arr = [];

		$user_id = '';
		$company_id = '';

		if ( is_object( $current_user ) ) {
			$user_id = $current_user->getId();
			$company_id = $current_user->getCompanyObject()->getId();
		} else {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $publicKeyCredentialUserEntity->getId() );
			if ( $ulf->getRecordCount() == 1 ) {
				$u_obj = $ulf->getCurrent();
				$user_id = $u_obj->getId();
				$company_id = $u_obj->getCompanyObject()->getId();
			}
		}

		$uilf = TTnew( 'UserIdentificationListFactory' ); /** @var UserIdentificationListFactory $uilf */
		$uilf->getByCompanyIdAndUserIdAndTypeId( $company_id, $user_id, 50 );
		if ( $uilf->getRecordCount() > 0 ) {
			foreach ( $uilf as $ui_obj ) { /** @var UserIdentificationFactory $ui_obj */
				$public_key_credential_source_arr[] = Webauthn\PublicKeyCredentialSource::createFromArray( json_decode( $ui_obj->getExtraValue(), true ) );
			}
		}

		return $public_key_credential_source_arr;
	}

	public function saveCredentialSource( PublicKeyCredentialSource $publicKeyCredentialSource ): void {
		global $current_user;

		if ( is_object( $current_user ) ) {
			$user_id = $current_user->getId();
		} else {
			$user_id = $publicKeyCredentialSource->getUserHandle();
		}

		$uif = TTnew( 'UserIdentificationFactory' ); /** @var UserIdentificationFactory $uif */
		$uif->setUser( $user_id );
		$uif->setType( 50 );
		$uif->setNumber( 0 );
		$uif->setValue( base64_encode( $publicKeyCredentialSource->getPublicKeyCredentialId() ) );
		$uif->setExtraValue( json_encode( $publicKeyCredentialSource ) );

		if ( $uif->isValid() ) {
			$uif->Save();
		}
	}
}