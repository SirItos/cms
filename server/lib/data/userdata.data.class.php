<?php
/*
 * Xibo - Digitial Signage - http://www.xibo.org.uk
 * Copyright (C) 2009-12 Daniel Garner
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('XIBO') or die(__('Sorry, you are not allowed to directly access this page.<br /> Please press the back button in your browser.'));

class Userdata extends Data
{
    public function __construct(database $db)
    {
        parent::__construct($db);
    }
    
    /**
     * Change a users password
     * @param <type> $userId
     * @param <type> $oldPassword
     * @param <type> $newPassword
     * @param <type> $retypedNewPassword
     * @return <type> 
     */
    public function ChangePassword($userId, $oldPassword, $newPassword, $retypedNewPassword, $forceChange = false)
    {
        // We can force the users password to change without having to provide the old one.
        // Is this a potential security hole - we must have validated that we are an admin to get to this point
        if (!$forceChange)
        {
            // Check the Old Password is correct
            if ($this->db->GetCountOfRows(sprintf("SELECT UserId FROM `user` WHERE UserID = %d AND UserPassword = '%s'", $userId, md5($oldPassword))) == 0)
                return $this->SetError(26000, __('Incorrect Password Provided'));
        }
        
        // Check the New Password and Retyped Password match
        if ($newPassword != $retypedNewPassword)
            return $this->SetError(26001, __('New Passwords do not match'));

        // Check password complexity
        if (!$this->TestPasswordAgainstPolicy($newPassword))
            return false;

        // Run the update
        if (!$this->db->query(sprintf("UPDATE `user` SET UserPassword = '%s' WHERE UserID = %d", md5($newPassword), $userId)))
        {
            trigger_error($this->db->error());
            return $this->SetError(25000, __('Could not edit Password'));
        }

        return true;
    }

    /**
     * Tests the supplied password against the password policy
     * @param <type> $password
     */
    public function TestPasswordAgainstPolicy($password)
    {
        // Check password complexity
        $policy = Config::GetSetting($this->db, 'USER_PASSWORD_POLICY');

        if ($policy != '')
        {
            $policyError = Config::GetSetting($this->db, 'USER_PASSWORD_ERROR');
            $policyError = ($policyError == '') ? __('Your password does not meet the required complexity') : $policyError;

            if(!preg_match($policy, $password, $matches))
                return $this->SetError(26001, $policyError);
        }

        return true;
    }
}
?>