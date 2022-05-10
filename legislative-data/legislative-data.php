<?php
/**
* Plugin Name: Legislative data
* Description: Provides the Votes and Legislation shortcodes
* Version: 1.0.1
* Author: Alterion
* License: GPL2
*/




/*
***********************************************
***********************************************

VOTES
Function that outputs vote data for a specific memberid with the shortcode [votes].  Member IDs can be found here: https://www.congress.gov/help/field-values/member-bioguide-ids 

***********************************************
***********************************************
*/

function get_votes( $atts ) {
  $shortcode_vars = shortcode_atts(
      array(
          'memberid' => '',
          'page' => '0'
      ), $atts);
  $memberId = $shortcode_vars['memberid'];
  $legislation_api_key = "";
  if( get_option('legislation_memberId') ){
    $memberId = get_option('legislation_memberId');
  }
  if( get_option('legislation_api_key') ){
    $legislation_api_key = get_option('legislation_api_key');
  }
    
  if(isset($_GET['votepage'])){
    if(is_numeric($_GET['votepage'])){
      $page = $_GET['votepage'];
      $offset = ($page - 1) * 20;

    } else {
      $page = 1;
      $offset = 0;
    }
  } else {
    $page = 1;
    $offset = 0;
  }
  $curl = curl_init();
    
  //Get bio data from Member ID
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.propublica.org/congress/v1/members/' . $memberId . '.json',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'X-API-Key: ' . $legislation_api_key
    ),
  ));
  $bio_response = json_decode(curl_exec($curl));
  curl_close($curl);
    
    foreach ($bio_response->results as $result ) {
          $lastName = $result->last_name;
          $firstName = $result->first_name;
          $roleresults = $result->roles;
          $position = "Senator";
    }
    $total_votes = 0;
    foreach ($roleresults as $roleresult ) {
      
          $total_votes = $total_votes + $roleresult->total_votes;
          $total_pages = round($total_votes/20)+1;
          
      //$num_results = $result->num_results;
      //$total_pages = ($num_results/20)+1;
  }
 
    
  //Query the propublica votes API using the member ID and parse the response
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.propublica.org/congress/v1/members/' . $memberId . '/votes.json?offset=' . $offset,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'X-API-Key: ' . $legislation_api_key
    ),
  ));
  $voteresponse = json_decode(curl_exec($curl));
  curl_close($curl);
  $i = 1;
  foreach ($voteresponse->results as $voteresult ) {
      $votes = $voteresult->votes;
      //$num_results = $result->num_results;
      //$total_pages = ($num_results/20)+1;
  }
    
//default styles
echo '<style>

.vote_question{
  color:#aaa;
  font-size:.8em;
}
.vote_table, .vote_table td, .vote_table th{
  padding:8px;
  font-size:.9em;
  border:none;
}
.vote_table th{
  background:black;
  color:white;
  text-align:left;
}
.vote_table .even{
  background:#f2f2f2;
  border-bottom:1px solid #ddd;
}
.vote_table .odd{
  background:#fff;
  border-bottom:1px solid #ddd;
}
.vote_bars{
  min-width:150px;
  border:1px solid #ddd;
  padding:5px;
  margin:3px;
  font-size:.7em;
  background:#f0f0f0;
  color:#999;
}
.yesbar{
  align:left;
  padding:2px;
  background:#333;
  height:20px;
  color:white;
  box-sizing: border-box;
}
.nobar{
  align:right;
  padding:2px;
  box-sizing: border-box;
  background:#fff;
  height:20px;
  color:black;
}
.vote_position,
.vote_result, .vote_link{
  color:black;
  font-size:1em;
  vertical-align: middle;
  text-align:center;
}
.vote_link a{
  color:dodgerblue;
  text-decoration:none;
}
.vote_link a:hover{
  color:black;
  text-decoration:underline;
}
.votes_pagination_container{
  text-align:center;
  margin:20px;
  text-align:center;
}
.votes_page_number{
    margin:5px;
}
.votes_page_number a {
  color:#999;
  padding:4px 8px;
}
.votes_page_number a:hover{
    background:#eee;
    color:#777;
}
.votes_page_number_active{
    background:#333;
    color:white;
    padding:4px 8px;
}
.page_next a, .page_prev a {
  color:#aaa;
  padding:0px 8px;
  font-size:1.3em;
}
.page_next a:hover, .page_prev a:hover {
  background:#eee;
  color:#777;
}

</style>';
  
  //Output the title and the table header.
  echo '
  <div class="vote_bio">
    <h2>Voting Record for ' . $position . ' <span class="vote_name">' . $firstName . ' ' . $lastName . '</span></h2>
  </div>
  <table class="vote_table" border="0" cellpadding="0" cellspacing="0" style="width:100%">
    <thead>
    <tr>
      <th class="vote_date_th">Vote&nbsp;Date</th>
      <th class="vote_description_th">Description</th>
      <th class="vote_myvote_th">My&nbsp;Vote</th>
      <th class="vote_result_th">Vote&nbsp;Result</th>
      <th class="vote_final_th">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
  ';
  //Get variables from response
  foreach ($votes as $vote ) {
    $vote_num = str_pad($vote->roll_call, 5, '0', STR_PAD_LEFT);
    $roll_call = $vote->roll_call;
    $congress = $vote->congress;
    $session = $vote->session;
    $bill_xml = 'https://www.senate.gov/legislative/LIS/roll_call_votes/vote' . $congress . $session . '/vote_' . $congress . '_' . $session . '_' . $vote_num . '.xml';
    $bill_html = 'https://www.senate.gov/legislative/LIS/roll_call_lists/roll_call_vote_cfm.cfm?congress=' . $congress . '&session=' . $session . '&vote=' . $vote_num;
    $vote_date = $vote->date;
    $datearray = explode('-', $vote_date);
    $year = $datearray[0];
    $totalyes =  $vote->total->yes;
    $totalno =  $vote->total->no;
    $totalpresent =  $vote->total->present;
    $totalnot_voting =  $vote->total->not_voting;
    $grand_total = $totalyes + $totalno;
    $percent_yes = ($totalyes/$grand_total) * 100;
    $percent_no = ($totalno/$grand_total) * 100;
    $govtrack_url = "https://www.govtrack.us/congress/votes/" . $congress . "-" . $year . "/s" . $roll_call;
    if ($i % 2 == 0) {
      $rowclass = "even";
    } else {
      $rowclass = "odd";
    }
?>

        <tr class="vote_row vote_row_<?php echo $i; ?> <?php echo $rowclass; ?>">
              <td class="vote_date" style="white-space: nowrap;"><?php echo $vote->date; ?></td>
              <td class="vote_detail"><div class="vote_question"><?php echo $vote->question; ?></div><div class="vote_description"><?php echo $vote->description; ?></div></td>
              <td class="vote_position"><?php echo $vote->position; ?></td>

              <td class="vote_results">

                <div class="vote_bars">
                  <div class="vote_result"><?php echo $vote->result; ?></div>
                    <div class="votebar yesbar" style="width:<?php echo $percent_yes;  ?>%; float:left; border:1px solid #ddd; <?php if($totalyes == 0){ echo 'display:none; ';}?>"><?php if($percent_yes > 10){ ?>Y<?php } ?></div>
                    <div class="votebar nobar" style="width:<?php echo $percent_no;  ?>%; float:left; text-align:right; border:1px solid #ddd; <?php if($totalno == 0){ echo 'display:none; ';}?>"><?php if($percent_no > 10){ ?>N<?php } ?></div>
                    <div class="votebar_text yesbar_text" style="width:50%; float:left;">
                        Yes: <?php echo $totalyes; ?>
                    </div>
                    <div class="votebar_text nobar_text" style="width:50%; text-align:right; float:left;">
                         No: <?php echo $totalno; ?>
                    </div>

                    <div style="clear:both"></div>
                </div>

              </td>
              <td class="vote_link"><a href="<?php echo $bill_html; ?>" target="_blank">Detail</a>
        </tr>
          <?php
          $i++;
      }
      echo "</tbody></table>";
     
    
    echo '<div class="votes_pagination_container">';
     if ($page != 1 && $page != 0){
        $prevpage = $page - 1;
        $prevtext = '<span class="page_prev vote_page_prev"><a href="?votepage=' . $prevpage . '">&#171;</a></span>';
    }
    if ($page != $total_pages){
        $nextpage = $page + 1;
        $nexttext = '<span class="page_next vote_page_next"><a href="?votepage=' . $nextpage . '">&#187;</a></span>';
    }
      $pagestart = 1;
      $pageend = 10;
      $prependtext = '';
      $appendtext = '...<span class="page_number votes_page_number page_number_inactive "><a href="?votepage=' . $total_pages . '">' . $total_pages . '</a></span>';
      if($page > 5){
         $pagestart = $page - 4;
         $pageend = $page + 5;
          $prependtext = '<span class="page_number votes_page_number page_number_inactive "><a href="?votepage=1">1...</a></span>';
          $appendtext = '...<span class="page_number votes_page_number page_number_inactive "><a href="?votepage=' . $total_pages . '">' . $total_pages . '</a></span>';
      }
      $final_range = $total_pages - 10;
      if($page > $final_range){
         $pagestart = $total_pages - 10;
         $pageend = $total_pages+1;
         $appendtext = '';
         $prependtext = '<span class="page_number votes_page_number page_number_inactive "><a href="?votepage=1">1...</a></span>';
      }
      if($total_pages < 5){
          $pagestart = 1;
          $pageend = $total_pages;
          $appendtext = '';
          $prependtext = '';
      }
      echo $prevtext . ' ';
      echo $prependtext . ' ';
      for ($k = $pagestart ; $k < $pageend; $k++){  
          $page_number = $k;
          if($page == $page_number){
            echo '<span class="page_number page_number_active votes_page_number votes_page_number_active">' . $page_number . '</span>';
          } else {
            echo '<span class="page_number_inactive page_number votes_page_number votes_page_number_inactive"><a  href="?votepage=' . $page_number . '">' . $page_number . '</a></span>';
          }
      }
     echo $appendtext . ' ';
    echo $nexttext . ' ';
      echo '</div>';
  


}
add_shortcode('votes', 'get_votes');






/*
***********************************************
***********************************************

LEGISLATION
Function to output legislative data for a specific member ID with the shortcode [legislation].  Member IDs can be found here: https://www.congress.gov/help/field-values/member-bioguide-ids 

***********************************************
***********************************************
*/
function get_legislation( $atts ) {
  $shortcode_vars = shortcode_atts(
      array(
          'memberid' => '',
          'page' => '1',
          'query' => ''
      ), $atts);
  $query = rawurlencode(strval($_GET['query']));
  $memberId = $shortcode_vars['memberid'];
  $page = $shortcode_vars['page'];
  $legislation_api_key = "";
  if( get_option('legislation_memberId') ){
    $memberId = get_option('legislation_memberId');
  }
  if( get_option('legislation_api_key') ){
    $legislation_api_key = get_option('legislation_api_key');
  }
  //page offset for pagination
  $offset = ($page - 1) * 20;
  if(isset($_GET['legislationPage'])){
    if(is_numeric($_GET['legislationPage'])){
      $page = $_GET['legislationPage'];

    } else {
      $page = 1;
    }
  } else {
    $page = 1;
  }
  $curl = curl_init();
  if(isset($_GET['query'])){
    $query = rawurlencode(strval($_GET['query']));
    $curl_url = strval('https://api.propublica.org/congress/v1/members/' . $memberId . '/bills/introduced.json?query=%22' . $query . '%22');
    
  } else {
    $query = "";
    $curl_url = 'https://api.propublica.org/congress/v1/members/' . $memberId . '/bills/introduced.json?offset=' . $offset;
  }
  curl_setopt_array($curl, array(
    CURLOPT_URL => $curl_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'X-API-Key: ' . $legislation_api_key
    ),
  ));

  $response = json_decode(curl_exec($curl));

  curl_close($curl);
  //echo $response;
  $i = 1;
  foreach ($response->results as $result ) {
      $thisresult = $result->bills;
      $num_results = $result->num_results;;
      $total_pages = ($num_results/20)+1;

  }

  //output the response
  echo '<div class="legislation_results_container">';
  foreach ($thisresult as $bill ) {

      $congress = $bill->congress;
      $bill_id = $bill->bill_id;
      $bill_type =  $bill->bill_type;
      $propublica_bill_uri = $bill->bill_uri;
      if(($query && !$bill->primary_subject)){

      } else {
      ?>
      <div id="bill_<?php echo $bill_id; ?>" class="legislation_item">
        <?php if($bill->primary_subject){ ?>
        <div class="legislation_subject">
         <a href="?query=<?php echo rawurlencode($bill->primary_subject); ?>"><?php echo $bill->primary_subject; ?></a>
        </div>
        <?php } ?>
        <div class="legislation_title">
          <h3><?php echo $bill->title; ?> <span  class="legislation_number"><a href="<?php echo $bill->congressdotgov_url; ?>">[<?php echo $bill->number; ?>]</a></span></h3>
        </div>
        <div class="legislation_date">
            <span class="legislation_label">Introduced:</span> <?php echo $bill->introduced_date; ?> by <span class="legislation_member"><?php echo $bill->sponsor_title; ?> <?php echo $bill->sponsor_name; ?><?php if($bill->committees){ ?> | <span class="legislation_committee"><?php echo $bill->committees; ?></span><?php } ?>
        </div>


        <?php if($bill->summary){ ?>
        <div class="legislation_summary">
            <span class="legislation_label">Summary:</span> <?php echo $bill->summary; ?>
        </div>
        <?php } ?>
        <div class="legislation_status">
             <span class="legislation_label">Latest status:</span>   <span class="legislation_status_date">[<?php echo $bill->latest_major_action_date; ?>] - </span>
             <?php echo $bill->latest_major_action; ?>
        </div>
        <div class="legislation_links">
          <span class="legislation_label">Read More:</span> <a href="<?php echo $bill->congressdotgov_url; ?>">Congress.gov</a> | <a href="<?php echo $bill->govtrack_url; ?>">GovTrack</a>
        </div>


    </div>

  <?php
    } // end if $query
    $i++;
  } //end bill loop
  echo '</div>'; //end legislation_results_container

  if(!isset($_GET['query'])){
  echo '<div class="legislation_pagination_container">';
      for ($k = 1 ; $k < $total_pages; $k++){
          $page_number = $k;
          if($page == $page_number){
            echo '<span class="page_number page_number_active legislation_page_number_active">' . $page_number . '</span>';
          } else {
            echo '<a class="page_number legislation_page_number" href="?page=' . $page_number . '"><span class="page_number_inactive legislation_page_number_inactive">' . $page_number . '</span></a>';
          }
      }
      echo '</div>';
  }
}
add_shortcode('legislation', 'get_legislation');


/*
Admin settings for Legislation Plugin
*/
function legislation_settings_init() {
	//register the admin settings
	//Default member ID. Example: C001056 | Member IDs can be found here: https://www.congress.gov/help/field-values/member-bioguide-ids
	add_option( 'legislation_memberId', '' );
	register_setting( 'legislation_options_group', 'legislation_memberId', 'legislation_callback' );

	//Register for an API key here: https://projects.propublica.org/api-docs/congress-api/
	add_option( 'legislation_api_key', '' );
	register_setting( 'legislation_options_group', 'legislation_api_key', 'legislation_callback' );
}
add_action( 'admin_init', 'legislation_settings_init' );

//Register Legislation Options Page
function legislation_register_options_page() {
  add_options_page( 'Legislation', 'Legislation', 'manage_options', 'legislation_options_page', 'legislation_options_page' );
}

add_action( 'admin_menu', 'legislation_register_options_page' );

//legislation settings admin screen (settings:legislation)

function legislation_options_page() {
?>

<div class="legislationOptionsContainer">
	<div>
		<h1>Legislation Shortcodes</h1>
    <h2>API integration with Propublica Congressional Data API</h2>
    <h3>Available shortcodes:</h3>
    <p><strong>[votes]</strong> - A paginated view of the Member's voting record.<br><strong>[legislation]</strong> - A paginated view of the Member's legislative history.</p>
	</div>
	<div class="legislationAdminForm">
    <p>Specify a member ID and API key below.</p>
	  <form method="post" action="options.php">
		  <?php settings_fields( 'legislation_options_group' ); ?>
		<fieldset style="border:1px solid #ccc; padding:20px; margin-top:20px; background:#fff">
		  <legend style="margin-bottom:5px; "><span class="dashicons dashicons-admin-generic"></span>Settings</legend>
			<label for="legislation_memberId" style="font-size:1.3em">Member ID:</label>
		  <input type="text" id="legislation_memberId" name="legislation_memberId" value="<?php echo get_option('legislation_memberId'); ?>" placeholder="E.g. C001056" required />
      </p>
      <p style="font-size:.8em"><em>Member IDs can be found here: <a href="https://www.congress.gov/help/field-values/member-bioguide-ids" target="_blank">Congressional Member Bioguide IDs</a></em></p>
			<label for="legislation_api_key" style="font-size:1.3em">API key:</label>
		  <input type="text" id="legislation_api_key" name="legislation_api_key" value="<?php echo get_option('legislation_api_key'); ?>" placeholder="E.g. XyV4geQxOm1jRHckzVKluMVbKMDMpqcAKWFZXlrB" required />
      <p style="font-size:.8em"><em>Register for an API key here: <a href="https://projects.propublica.org/api-docs/congress-api/" target="_blank">Propublica Congress API</a></em></p>
      </p>
		</fieldset>
		<?php submit_button(); ?>
	  </form>
	</div>
</div>

<?php


}
