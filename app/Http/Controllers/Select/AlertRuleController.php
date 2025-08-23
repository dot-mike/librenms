<?php

/**
 * AlertRuleController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 LibreNMS
 * @author     LibreNMS Contributors
 */

namespace App\Http\Controllers\Select;

use App\Models\AlertRule;

class AlertRuleController extends SelectController
{
    protected function rules()
    {
        return [];
    }

    protected function searchFields($request)
    {
        return ['name'];
    }

    protected function baseQuery($request)
    {
        return AlertRule::query()
            ->select('id', 'name', 'severity')
            ->orderBy('name');
    }

    public function formatItem($alertRule)
    {
        /** @var AlertRule $alertRule */
        return [
            'id' => $alertRule->id,
            'text' => $alertRule->name,
        ];
    }
}
