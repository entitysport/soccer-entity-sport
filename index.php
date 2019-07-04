<?php
/* 
firstly you have to put authentication data in to api_tokon.json file.

Entity library use function api_request() for get api data.
In this function you have pass some variable like this :
    api_request( $path = u want data , $args = arguments array which need for get with formate)
    $path = 'season/2018/competitions';
    $args = array('per_page'=>10 ,'paged'=>20); where paged is which page u wana get data
*/



# iclude this file for calling http request via curl. 
include_once('entity-api.php' );

#include this for all function you need for get data with diffrent api.

/*
for get data for all season call get_seasons_data()
for get data for perticular season call get_seasons_data($sid,$args)...$sid eg- 2018,18-19,etc.

for get data for all competitions call get_competitions_data($cid=0,$args)
here args use for filter data you get. Like paged,per_page,status with those variables.
status status code 1 = upcoming, 2 = result, 3 = live.

get perticular competition info with stats ,squads , matches call get_competitions_data($cid,$args)
this  get_competition_squad($cid) ,get_competition_matches($cid), get_competition_statstic($cid)

for get data for all metches call get_matches_data($mid=0,$args=array())
here args use for filter data you get. Like paged,per_page,status with those variables.
here you can filter matches between dates start_date and end_date with formate yyyy-mm-dd; 
status status code 1 = upcoming, 2 = result, 3 = live.

get perticular metches info with stats  , fantacy call get_matches_stats($mid,$args) get_matches_fantasy($mid,$args)

for get data for all teams call get_teams_data($tid=0,$args)
for get data for all teams maches call get_teams_maches($tid,$args)

for get data for all players call get_players_data($pid=0,$args)
for get data for plater profile call get_players_data($pid,$args)

If you do not send the id than u get all data other perticular id info.

*/
include_once('get-all-apis.php' );


/*
for example in your codignator project
inlude file index.php
require_once( APPPATH . 'libraries/soccer-entity-master/index.php' );

and than class Entity_sports()
        $entity = new Entity_sports();
        $result = $entity->get_seasons_data();
        this $result is your output


        this library is use curl php method so you have to check curl is enable in your php code</so>
*/