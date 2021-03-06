<?php
/**
 *
 * ThinkUp/tests/TestOfDAOFactory.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie, Christoffer Viken
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Test of DAOFactory
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie, Christoffer Viken
 * @author Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterInstanceMySQLDAO.php';

class TestOfDAOFactory extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();
         
        // test table for our test dao
        $test_table_sql = 'CREATE TABLE tu_test_table(' .
            'id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,' . 
            'test_name varchar(20),' .
            'test_id int(11),' .
            'unique key test_id_idx (test_id)' .
            ')';
        $this->testdb_helper->runSQL($test_table_sql);

        //some test data as well
        for($i = 1; $i <= 20; $i++) {
            $builders[] = FixtureBuilder::build('test_table', array('test_name'=>'name'.$i, 'test_id'=>$i));
        }
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        //make sure our db_type is set to the default...
        Config::getInstance()->setValue('db_type', 'mysql');
    }

    /*
     * test fetching the proper db_type
     */
    public function testDAODBType() {
        Config::getInstance()->setValue('db_type', null);
        $type = DAOFactory::getDBType();
        $this->assertEqual($type, 'mysql', 'should default to mysql');

        Config::getInstance()->setValue('db_type', 'some_sql_server');
        $type = DAOFactory::getDBType();
        $this->assertEqual($type, 'some_sql_server', 'is set to some_sql_server');
    }

    /*
     * test init DAOs, bad params and all...
     */
    public function testGetTestDAO() {
        // no map for this DAO
        try {
            DAOFactory::getDAO('NoSuchDAO');
            $this->fail('should throw an exception');
        } catch(Exception $e) {
            $this->assertPattern('/No DAO mapping defined for: NoSuchDAO/', $e->getMessage(), 'no dao mapping');
        }

        // invalid db type for this dao
        Config::getInstance()->setValue('db_type', 'nodb');
        try {
            DAOFactory::getDAO('TestDAO');
            $this->fail('should throw an exception');
        } catch(Exception $e) {
            $this->assertPattern("/No db mapping defined for 'TestDAO'/", $e->getMessage(), 'no dao db_type mapping');
        }

        // valid mysql test dao
        Config::getInstance()->setValue('db_type', 'mysql');
        $test_dao = DAOFactory::getDAO('TestDAO');
        $this->assertIsA($test_dao, 'TestMysqlDAO', 'we are a mysql dao');
        $data_obj = $test_dao->selectRecord(1);
        $this->assertNotNull($data_obj);
        $this->assertEqual($data_obj->test_name, 'name1');
        $this->assertEqual($data_obj->test_id, 1);

        // valid fuax test dao
        Config::getInstance()->setValue('db_type', 'faux');
        $test_dao = DAOFactory::getDAO('TestDAO');
        $this->assertIsA($test_dao, 'TestFauxDAO', 'we are a mysql dao');
        $data_obj = $test_dao->selectRecord(1);
        $this->assertNotNull($data_obj);
        $this->assertEqual($data_obj->test_name, 'Mojo Jojo');
        $this->assertEqual($data_obj->test_id, 2001);
    }
    /**
     * Test get InstanceDAO
     */
    public function testGetInstanceDAO(){
        $dao = DAOFactory::getDAO('InstanceDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'InstanceMySQLDAO');
    }

    /**
     * Test get FollowDAO
     */
    public function testGetFollowDAO(){
        $dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'FollowMySQLDAO');
    }

    /**
     * Test get PostErrorDAO
     */
    public function testGetPostErrorDAO(){
        $dao = DAOFactory::getDAO('PostErrorDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'PostErrorMySQLDAO');
    }
    /**
     * Test get PostDAO
     */
    public function testGetPostDAO(){
        $dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'PostMySQLDAO');
    }

    /**
     * Test get UserDAO
     */
    public function testGetUserDAO(){
        $dao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'UserMySQLDAO');
    }

    /**
     * Test get UserErrorDAO
     */
    public function testGetUserErrorDAO(){
        $dao = DAOFactory::getDAO('UserErrorDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'UserErrorMySQLDAO');
    }

    /**
     * Test get OwnerDAO
     */
    public function testGetOwnerDAO(){
        $dao = DAOFactory::getDAO('OwnerDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'OwnerMySQLDAO');
    }

    /**
     * Test get OwnerDAO without a config file, override with array of config values
     */
    public function testGetOwnerDAONoConfigFile(){
        $this->removeConfigFile();
        Config::destroyInstance();
        $cfg_values = array("table_prefix"=>"tu_", "db_host"=>"localhost");
        $config = Config::getInstance($cfg_values);
        $dao = DAOFactory::getDAO('OwnerDAO', $cfg_values);
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'OwnerMySQLDAO');
        $this->restoreConfigFile();
    }

    /**
     * Test get LinkDAO
     */
    public function testGetLinkDAO(){
        $dao = DAOFactory::getDAO('LinkDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'LinkMySQLDAO');
    }

    /**
     * Test get HashtagDAO
     */
    public function testGetHashtagDAO(){
        $dao = DAOFactory::getDAO('HashtagDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'HashtagMySQLDAO');
    }

    /**
     * Test get MentionDAO
     */
    public function testGetMentionDAO(){
        $dao = DAOFactory::getDAO('MentionDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'MentionMySQLDAO');
    }

    /**
     * Test get PlaceDAO
     */
    public function testGetPlaceDAO(){
        $dao = DAOFactory::getDAO('PlaceDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'PlaceMySQLDAO');
    }

    /**
     * Test get StreamDataDAO
     */
    public function testGetStreamDataDAO(){
        $dao = DAOFactory::getDAO('StreamDataDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'StreamDataMySQLDAO');
    }

    /**
     * Test get OwnerInstanceDAO
     */
    public function testGetOwnerInstanceDAO() {
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $this->assertNotNull($owner_instance_dao);
        $this->assertIsA($owner_instance_dao, 'OwnerInstanceMySQLDAO');
    }

    /**
     * Test get PluginDAO
     */
    public function testGetPluginDAO() {
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $this->assertNotNull($plugin_dao);
        $this->assertIsA($plugin_dao, 'PluginMySQLDAO');
    }

    /**
     * Test get PluginOptionDAO
     */
    public function testGetPluginOptionDAO() {
        $plugin_dao = DAOFactory::getDAO('PluginOptionDAO');
        $this->assertNotNull($plugin_dao);
        $this->assertIsA($plugin_dao, 'PluginOptionMySQLDAO');
    }

    /**
     * Test get FollowerCountDAO
     */
    public function testGetFollowerCountDAO() {
        $plugin_dao = DAOFactory::getDAO('FollowerCountDAO');
        $this->assertNotNull($plugin_dao);
        $this->assertIsA($plugin_dao, 'FollowerCountMySQLDAO');
    }

    /**
     * Test get MutexDAO
     */
    public function testGetMutexDAO() {
        $mutex_dao = DAOFactory::getDAO('MutexDAO');
        $this->assertNotNull($mutex_dao);
        $this->assertIsA($mutex_dao, 'MutexMySQLDAO');
    }

    /**
     * Test get OptionDAO
     */
    public function testGetOptionDAO() {
        $dao = DAOFactory::getDAO('OptionDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'OptionMySQLDAO');
    }
    /**
     * Test get BackupDAO
     */
    public function testGetBackupDAO() {
        $dao = DAOFactory::getDAO('BackupDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'BackupMySQLDAO');
    }
    /**
     * Test get FavoritePostDAO
     */
    public function testGetFavoritePostDAO() {
        $dao = DAOFactory::getDAO('FavoritePostDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'FavoritePostMySQLDAO');
    }
    /**
     * Test get InviteDAO
     */
    public function testGetInviteDAO() {
        $dao = DAOFactory::getDAO('InviteDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'InviteMySQLDAO');
    }
    /**
     * Test get TwitterInstanceDAO
     */
    public function testGetTwitterInstanceDAO() {
        $dao = DAOFactory::getDAO('TwitterInstanceDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'TwitterInstanceMySQLDAO');
    }
    /**
     * Test get PostExportDAO
     */
    public function testGetPostExportDAO() {
        $dao = DAOFactory::getDAO('ExportDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'ExportMySQLDAO');
    }

    /**
     * Test get StreamProcDAO
     */
    public function testGetStreamProcsDAO() {
        $dao = DAOFactory::getDAO('StreamProcDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'StreamProcMySQLDAO');
    }

    public function testGetGroupDAO() {
        $dao = DAOFactory::getDAO('GroupDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'GroupMySQLDAO');
    }

    public function testGetGroupMemberDAO() {
        $dao = DAOFactory::getDAO('GroupMemberDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'GroupMemberMySQLDAO');
    }

    public function testGetGroupMembershipDAO() {
        $dao = DAOFactory::getDAO('GroupMembershipCountDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'GroupMembershipCountMySQLDAO');
    }

    /**
     * Test get TableStatsDAO
     */
    public function testGetTableStatsDAO() {
        $dao = DAOFactory::getDAO('TableStatsDAO');
        $this->assertNotNull($dao);
        $this->assertIsA($dao, 'TableStatsMySQLDAO');
    }
    /**
     * Test get InstallerDAO without a config file, override with array of config values
     */
    public function testGetInstallerDAONoConfigFile(){
        $this->removeConfigFile();
        Config::destroyInstance();
        $cfg_values = array("table_prefix"=>"tu_", "db_host"=>"localhost");
        $config = Config::getInstance($cfg_values);
        $dao = DAOFactory::getDAO('InstallerDAO', $cfg_values);
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'InstallerMySQLDAO');
        $result = $dao->getTables();
        $this->assertEqual(sizeof($result), 28);
        $this->assertEqual($result[0], $cfg_values["table_prefix"].'encoded_locations');
        $this->restoreConfigFile();
    }
}
