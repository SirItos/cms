<?php
/*
 * Spring Signage Ltd - http://www.springsignage.com
 * Copyright (C) 2015 Spring Signage Ltd
 * (UserGroupFactory.php)
 */


namespace Xibo\Factory;


use Xibo\Entity\UserGroup;
use Xibo\Exception\NotFoundException;
use Xibo\Helper\Log;
use Xibo\Storage\PDOConnect;

class UserGroupFactory
{
    /**
     * @param array $sortOrder
     * @param array $filterBy
     * @return array[DisplayProfile]
     * @throws NotFoundException
     */
    public static function query($sortOrder = null, $filterBy = null)
    {
        $entries = array();
        $params = array();

        try {
            $sql = '
            SELECT 	group.group,
				group.groupId
              FROM `group`
             WHERE IsUserSpecific = 0 AND IsEveryone = 0
            ';

            // Sorting?
            if (is_array($sortOrder))
                $sql .= 'ORDER BY ' . implode(',', $sortOrder);

            Log::sql($sql, $params);

            foreach (PDOConnect::select($sql, $params) as $row) {
                $entries[] = (new UserGroup())->hydrate($row);
            }

            return $entries;

        } catch (\Exception $e) {

            Log::error($e);

            throw new NotFoundException();
        }
    }
}