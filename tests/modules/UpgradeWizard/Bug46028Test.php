<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


/**
 * Bug46098Test
 *
 * This class contains the unit test to check that the repairSearchFields method will create a SearchFields.php file
 * to correctly handle range searching for date fields.
 *
 */

require_once('modules/UpgradeWizard/uw_utils.php');

class Bug46028Test extends Sugar_PHPUnit_Framework_TestCase
{

var $customOpportunitiesSearchFields;
var $opportunitiesSearchFields;

public function setUp()
{
    if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'))
    {
        $this->customOpportunitiesSearchFields = file_get_contents('custom/modules/Opportunities/metadata/SearchFields.php');
        unlink('custom/modules/Opportunities/metadata/SearchFields.php');
    }

    if(file_exists('modules/Opportunities/metadata/SearchFields.php'))
    {
        $this->opportunitiesSearchFields = file_get_contents('modules/Opportunities/metadata/SearchFields.php');
        unlink('modules/Opportunities/metadata/SearchFields.php');
    }

$searchFieldContents = <<<EOQ
<?php
\$searchFields['Opportunities'] =
array (
    'name' => array( 'query_type'=>'default'),
    'account_name'=> array('query_type'=>'default','db_field'=>array('accounts.name')),
    'amount'=> array('query_type'=>'default'),
    'next_step'=> array('query_type'=>'default'),
    'probability'=> array('query_type'=>'default'),
    'lead_source'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'lead_source_dom', 'template_var' => 'LEAD_SOURCE_OPTIONS'),
    'opportunity_type'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'opportunity_type_dom', 'template_var' => 'TYPE_OPTIONS'),
    'sales_stage'=> array('query_type'=>'default', 'operator'=>'=', 'options' => 'sales_stage_dom', 'template_var' => 'SALES_STAGE_OPTIONS', 'options_add_blank' => true),
    'current_user_only'=> array('query_type'=>'default','db_field'=>array('assigned_user_id'),'my_items'=>true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
    'assigned_user_id'=> array('query_type'=>'default'),
    'favorites_only' => array(
    'query_type'=>'format',
                'operator' => 'subquery',
                'subquery' => 'SELECT sugarfavorites.record_id FROM sugarfavorites
                                    WHERE sugarfavorites.deleted=0
                                        and sugarfavorites.module = \'Opportunities\'
                                        and sugarfavorites.assigned_user_id = \'{0}\'',
                'db_field'=>array('id')),
);
?>
EOQ;

    file_put_contents('modules/Opportunities/metadata/SearchFields.php', $searchFieldContents);
}

public function tearDow()
{
    if(!empty($this->customOpportunitiesSearchFields))
    {
        file_put_contents('custom/modules/Opportunities/metadata/SearchFields.php', $this->customOpportunitiesSearchFields);
    } else if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php')) {
        unlink('custom/modules/Opportunities/metadata/SearchFields.php');
    }

    if(!empty($this->opportunitiesSearchFields))
    {
        file_put_contents('modules/Opportunities/metadata/SearchFields.php', $this->opportunitiesSearchFields);
    }
}

public function testRepairSearchFields()
{
    repairSearchFields('modules/Opportunities/metadata/SearchFields.php');
    $this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
    require('custom/modules/Opportunities/metadata/SearchFields.php');
    $this->assertArrayHasKey('range_date_entered', $searchFields['Opportunities']);
    $this->assertArrayHasKey('start_range_date_entered', $searchFields['Opportunities']);
    $this->assertArrayHasKey('end_range_date_entered', $searchFields['Opportunities']);
    $this->assertArrayHasKey('range_date_modified', $searchFields['Opportunities']);
    $this->assertArrayHasKey('start_range_date_modified', $searchFields['Opportunities']);
    $this->assertArrayHasKey('end_range_date_modified', $searchFields['Opportunities']);
}

}
?>