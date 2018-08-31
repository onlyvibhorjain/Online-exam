<style>
 td{
		font-size:14px;
		padding:4px;
	}
	
	
</style>


<script>
var countKeyPressed = 1;
document.onselectstart = function()
{
    window.getSelection().removeAllRanges();
};

document.onkeydown = function (e) {
	var msg = false;
    e = e || window.event;//Get event
	if (e.keyCode == 123) { // Prevent F12
        return false;
    } else if (e.ctrlKey && e.shiftKey && e.keyCode == 73) { // Prevent Ctrl+Shift+I 
		msg = true;
    }else if((e.which == 85) || (e.which == 67) && e.ctrlKey)
	{
		msg = true;
	}
	if(msg){
		if(countKeyPressed<4){
			countKeyPressed ++;
			alert("Un-authorize key pressed. It may cause to disqualification.");
			return false;
		}
		else{
			alert("You are disqualified");
			window.location="<?php echo site_url('quiz/submit_quiz/');?>";
			return false;
		}
	}
};
$(document).on("contextmenu", function (e) {        
    e.preventDefault();
});
var Timer;
var TotalSeconds;


function CreateTimer(TimerID, Time) {
Timer = document.getElementById(TimerID);
TotalSeconds = Time;

UpdateTimer()
window.setTimeout("Tick()", 1000);
}

function Tick() {
if (TotalSeconds <= 0) {
alert("Time's up!")
return;
}

TotalSeconds -= 1;
UpdateTimer()
window.setTimeout("Tick()", 1000);
}

function UpdateTimer() {
var Seconds = TotalSeconds;

var Days = Math.floor(Seconds / 86400);
Seconds -= Days * 86400;

var Hours = Math.floor(Seconds / 3600);
Seconds -= Hours * (3600);

var Minutes = Math.floor(Seconds / 60);
Seconds -= Minutes * (60);


var TimeStr = ((Days > 0) ? Days + " days " : "") + LeadingZero(Hours) + ":" + LeadingZero(Minutes) + ":" + LeadingZero(Seconds)


Timer.innerHTML = TimeStr;
}


function LeadingZero(Time) {

return (Time < 10) ? "0" + Time : + Time;

}

//var myCountdown1 = new Countdown({time:<?php echo $seconds;?>, rangeHi:"hour", rangeLo:"second"});
setTimeout(submitform,'<?php echo $seconds * 1000;?>');
function submitform(){
alert('Time Over');
window.location="<?php echo site_url('quiz/submit_quiz/');?>";
}
</script>


<script>
	$(document).ready(function(){
		var stopFetch = false;

		// if (window.location.protocol !== "file:") {
		// $("input[name=apiUrl]").attr('value', window.location.origin);
		// }

		function createQueryParameters(type = "Request") {
		var parameters = [];
		if ($(`input[name=base64Encoded${type}]`).is(":checked")) {
			parameters.push("base64_encoded=true");
		}

		var fields = $("input[name=fields]").val();
		if (fields.length != 0) {
			parameters.push(`fields=${fields}`);
		}

		var authnHeader = $("input[name=authnHeader]").val();
		var authnToken = $("input[name=authnToken]").val();
		if (authnToken.length != 0) {
			parameters.push(`${authnHeader}=${authnToken}`);
		}

		var authzHeader = $("input[name=authzHeader]").val();
		var authzToken = $("input[name=authzToken]").val();
		if (authzToken.length != 0) {
			parameters.push(`${authzHeader}=${authzToken}`);
		}

		if ($("input[name=waitResponse]").is(":checked")) {
			parameters.push("wait=true");
		}

		if (parameters.length == 0) {
			return "";
		}

		var queryParameters = "?";
		for (var i = 0; i < parameters.length - 1; i++) {
			queryParameters += parameters[i] + "&";
		}

		return queryParameters + parameters[parameters.length - 1];
		}

		function resetButtons() {
		stopFetch = false;
		$("#run").removeAttr("disabled");
		$("#stop").prop("disabled", true);
		$("#panel").css('background-color', '#00F20D');
		}

		function appendToLog(text) {
		//$("#log").text($("#log").text() + text + "\n");
		$("#log").text(text);
		$('html, body').animate({
				scrollTop: $("body")[0].scrollHeight
			}, 500);
		}

		function fetchSubmission(apiUrl, token) {
		var queryParameters = createQueryParameters("Response");
		appendToLog(`[Request ${new Date().toLocaleString()}] GET ${apiUrl}/submissions/${token}${queryParameters}`);
		$.ajax({
			url: apiUrl + "/submissions/" + token + queryParameters,
			type: "GET",
			async: true,
			success: function(data, textStatus, jqXHR) {
			appendToLog(`[Response ${new Date().toLocaleString()}] ${jqXHR.status} ${jqXHR.statusText}\n${JSON.stringify(data, null, 4)}\n`);
			if ((data.status === undefined || data.status.id <= 2) && (data.status_id === undefined || data.status_id <= 2) && !stopFetch) {
				setTimeout(fetchSubmission.bind(null, apiUrl, token), 1500);
			} else if (!stopFetch) {
				appendToLog(`[DONE ${new Date().toLocaleString()}]\n\n\n`);
				resetButtons();
			} else {
				appendToLog(`[STOPPED ${new Date().toLocaleString()}]\n\n\n`);
				resetButtons();
			}
			},
			error: function handleError(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR);
					console.log(textStatus);
					appendToLog(`[Response ${new Date().toLocaleString()}] \n ${jqXHR.responseText} ${jqXHR.status}`);
					resetButtons();
			}
		});
		}

		$("#run").click(function() {
			$(this).prop("disabled", true);
			$("#stop").removeAttr("disabled");
			$("#panel").css('background-color', '#F2000D');

			var apiUrl = $("input[name=apiUrl]").val();
			var sourceCode = $(".code").val();
			var languageId = $("#languageId").val();
			var numberOfRuns = $("input[name=numberOfRuns]").val();
			var stdin = $("textarea[name=stdin]").val();
			var expectedOutput = $("#expected").val().replace("<p>","");
			var cpuTimeLimit = $("input[name=cpuTimeLimit]").val();
			var cpuExtraTime = $("input[name=cpuExtraTime]").val();
			var wallTimeLimit = $("input[name=wallTimeLimit]").val();
			var memoryLimit = $("input[name=memoryLimit]").val();
			var stackLimit = $("input[name=stackLimit]").val();
			var maxProcessesAndOrThreads = $("input[name=maxProcessesAndOrThreads]").val();
			var enablePerProcessAndThreadTimeLimit = $("input[name=enablePerProcessAndThreadTimeLimit]:checked").val() === "true";
			var enablePerProcessAndThreadMemoryLimit = $("input[name=enablePerProcessAndThreadMemoryLimit]:checked").val() === "true";
			var maxFileSize = $("input[name=maxFileSize]").val();
			var wait = $("input[name=waitResponse]").is(":checked");

			var queryParameters = createQueryParameters();
			if ($("input[name=base64EncodedRequest]").is(":checked")) {
				sourceCode = btoa(sourceCode);
				stdin = btoa(stdin);
				expectedOutput = btoa(expectedOutput);
			}
			if ($("input[name=sourceCodeIsNull]").is(":checked")) {
				sourceCode = null;
			}
			if ($("input[name=languageIdIsNull]").is(":checked")) {
				languageId = null;
			}
			if ($("input[name=numberOfRunsIsNull]").is(":checked")) {
				numberOfRuns = null;
			}
			if ($("input[name=stdinIsNull]").is(":checked")) {
				stdin = null;
			}
			if ($("input[name=expectedOutputIsNull]").is(":checked")) {
				expectedOutput = null;
			}
			if ($("input[name=cpuTimeLimitIsNull]").is(":checked")) {
				cpuTimeLimit = null;
			}
			if ($("input[name=cpuExtraTimeIsNull]").is(":checked")) {
				cpuExtraTime = null;
			}
			if ($("input[name=wallTimeLimitIsNull]").is(":checked")) {
				wallTimeLimit = null;
			}
			if ($("input[name=memoryLimitIsNull]").is(":checked")) {
				memoryLimit = null;
			}
			if ($("input[name=stackLimitIsNull]").is(":checked")) {
				stackLimit = null;
			}
			if ($("input[name=maxProcessesAndOrThreadsIsNull]").is(":checked")) {
				maxProcessesAndOrThreads = null;
			}
			if ($("input[name=enablePerProcessAndThreadTimeLimit]:checked").val() === "null") {
				enablePerProcessAndThreadTimeLimit = null;
			}
			if ($("input[name=enablePerProcessAndThreadMemoryLimit]:checked").val() === "null") {
				enablePerProcessAndThreadMemoryLimit = null;
			}
			if ($("input[name=maxFileSizeIsNull]").is(":checked")) {
				maxFileSize = null;
			}
			var data = {
				source_code: sourceCode,
				language_id: languageId,
				number_of_runs: numberOfRuns,
				stdin: stdin,
				expected_output: expectedOutput.replace("</p>",""),
				cpu_time_limit: cpuTimeLimit,
				cpu_extra_time: cpuExtraTime,
				wall_time_limit: wallTimeLimit,
				memory_limit: memoryLimit,
				stack_limit: stackLimit,
				max_processes_and_or_threads: maxProcessesAndOrThreads,
				enable_per_process_and_thread_time_limit: enablePerProcessAndThreadTimeLimit,
				enable_per_process_and_thread_memory_limit: enablePerProcessAndThreadMemoryLimit,
				max_file_size: maxFileSize
			};

			//appendToLog(`[Request ${new Date().toLocaleString()}] POST ${apiUrl}/submissions${queryParameters}\n${JSON.stringify(data, null, 4)}`);
			$.ajax({
				url: apiUrl + "/submissions" + queryParameters,
				type: "POST",
				async: true,
				contentType: "application/json",
				data: JSON.stringify(data),
				success: function(data, textStatus, jqXHR) {
					if(data.status.description=="Compilation Error"){
						appendToLog(`[Response ${new Date().toLocaleString()}] ${jqXHR.status} ${jqXHR.statusText}  
									\n status : ${data.status.description} \n result : \n${data.stdout}`);
					}else{
						appendToLog(`[Response ${new Date().toLocaleString()}] ${jqXHR.status} ${jqXHR.statusText}  
									\n result : \n${data.stdout}`);
					}
				if (!wait) {
					setTimeout(fetchSubmission.bind(null, apiUrl, data.token), 1500);
				} else {
					//appendToLog(`[DONE ${new Date().toLocaleString()}]\n\n\n`);
					resetButtons();
				}
				},
				error: function handleError(jqXHR, textStatus, errorThrown) {
				    console.log(jqXHR);
					console.log(textStatus);
					appendToLog(`[Response ${new Date().toLocaleString()}] \n ${jqXHR.responseText} ${jqXHR.status}`);
					resetButtons();
				} 
			});
		});

		$("#stop").click(function() {
		stopFetch = true;
		});

		$("#clearLog").click(function() {
		$("#log").html("");
		});

		$("#backToTop").click(function() {
		$('html, body').animate({
				scrollTop: 0
			}, 50);
		});
	});
</script>

<div class="container" >
<div class="save_answer_signal" id="save_answer_signal2"></div>
<div class="save_answer_signal" id="save_answer_signal1"></div>

<div class="col-md-12 row" style="width:100%;padding:0px;" >
	<div class="col-md-4">
		<img class="img-responsive" style="height:8%;" src="<?php echo base_url('images/logo.png');?>"></img>
	</div>
	<div class="col-md-4" style="text-align:center;">
		<h4><?php echo $title;?></h4>
	</div>
	<div class="col-md-4" style="padding-top:1%;font-size:16px;">
		<p style="float:right;">Time left: 
			<span id='timer' >
				<script type="text/javascript">window.onload = CreateTimer("timer", <?php echo $seconds;?>);</script>
			</span>
		</p>
	</div>
</div>
	
<div style="clear:both;"></div>

<!-- Category button -->

 <div class="row"  >
<?php 
$categories=explode(',',$quiz['categories']);
$category_range=explode(',',$quiz['category_range']);
 
function getfirstqn($cat_keys='0',$category_range){
	if($cat_keys==0){
		return 0;
	}else{
		$r=0;
		for($g=0; $g < $cat_keys; $g++){
		$r+=$category_range[$g];	
		}
		return $r;
	}
	
	
}


if(count($categories) > 1 ){
	$jct=0;
	foreach($categories as $cat_key => $category){
?>
<a href="javascript:switch_category('cat_<?php echo $cat_key;?>');"   class="btn btn-info"  style="cursor:pointer;"><?php echo $category;?></a>
<input type="hidden" id="cat_<?php echo $cat_key;?>" value="<?php echo getfirstqn($cat_key,$category_range);?>">
<?php 
}
}
?>
</div> 

   
 
 <div class="row"  style="margin-top:5px;">
 <div class="col-md-8">
<form method="post" class="unselectable" action="<?php echo site_url('quiz/submit_quiz/'.$quiz['rid']);?>" id="quiz_form" >
<input type="hidden" name="rid" value="<?php echo $quiz['rid'];?>">
<input type="hidden" name="noq" value="<?php echo $quiz['noq'];?>">
<input type="hidden" name="individual_time"  id="individual_time" value="<?php echo $quiz['individual_time'];?>">
 
<?php 
$abc=array(
'0'=>'A',
'1'=>'B',
'2'=>'C',
'3'=>'D',
'4'=>'E',
'6'=>'F',
'7'=>'G',
'8'=>'H',
'9'=>'I',
'10'=>'J',
'11'=>'K'
);
foreach($questions as $qk => $question){
?>
 
 <div id="q<?php echo $qk;?>" class="question_div">
		
		<div class="question_container" >
		 <?php echo $this->lang->line('question');?> <?php echo $qk+1;?>)<br>
		 <?php echo $question['question'];?>
		 <?php if($question['question_type']==$this->lang->line('long_answer')){
			  echo "<br/><div>".$question['description']."</div>";
		?>
			  <textarea id='expected' style="display:none;"><?php echo $question['description'] ?></textarea>
		<?php
			}
		  ?>
		 </div>
		<div class="option_container" >
		 <?php 
		 // multiple single choice
		 if($question['question_type']==$this->lang->line('multiple_choice_single_answer')){
			 
			 			 			 $save_ans=array();
			 foreach($saved_answers as $svk => $saved_answer){
				 if($question['qid']==$saved_answer['qid']){
					$save_ans[]=$saved_answer['q_option'];
				 }
			 }
			 
			 
			 ?>
			 <input type="hidden"  name="question_type[]"  id="q_type<?php echo $qk;?>" value="1">
			 <?php
			$i=0;
			foreach($options as $ok => $option){
				if($option['qid']==$question['qid']){
			?>
			 
		<div class="op"><?php echo $abc[$i];?>) <input type="radio" name="answer[<?php echo $qk;?>][]"  id="answer_value<?php echo $qk.'-'.$i;?>" value="<?php echo $option['oid'];?>"   <?php if(in_array($option['oid'],$save_ans)){ echo 'checked'; } ?>  > <?php echo $option['q_option'];?> </div>
			 
			 
			 <?php 
			 $i+=1;
				}else{
				$i=0;	
					
				}
			}
		 }
			
// multiple_choice_multiple_answer	

		 if($question['question_type']==$this->lang->line('multiple_choice_multiple_answer')){
			 			 $save_ans=array();
			 foreach($saved_answers as $svk => $saved_answer){
				 if($question['qid']==$saved_answer['qid']){
					$save_ans[]=$saved_answer['q_option'];
				 }
			 }
			 
			 ?>
			 <input type="hidden"  name="question_type[]"  id="q_type<?php echo $qk;?>" value="2">
			 <?php
			$i=0;
			foreach($options as $ok => $option){
				if($option['qid']==$question['qid']){
			?>
			 
		<div class="op"><?php echo $abc[$i];?>) <input type="checkbox" name="answer[<?php echo $qk;?>][]" id="answer_value<?php echo $qk.'-'.$i;?>"   value="<?php echo $option['oid'];?>"  <?php if(in_array($option['oid'],$save_ans)){ echo 'checked'; } ?> > <?php echo $option['q_option'];?> </div>
			 
			 
			 <?php 
			 $i+=1;
				}else{
				$i=0;	
					
				}
			}
		 }
			 
	// short answer	

		 if($question['question_type']==$this->lang->line('short_answer')){
			 			 $save_ans="";
			 foreach($saved_answers as $svk => $saved_answer){
				 if($question['qid']==$saved_answer['qid']){
					$save_ans=$saved_answer['q_option'];
				 }
			 }
			 ?>
			 <input type="hidden"  name="question_type[]"  id="q_type<?php echo $qk;?>" value="3" >
			 <?php
			 ?>
			 
		<div class="op"> 
		<?php echo $this->lang->line('answer');?> 
		<input type="text" name="answer[<?php echo $qk;?>][]" value="<?php echo $save_ans;?>" id="answer_value<?php echo $qk;?>"   >  
		</div>
			 
			 
			 <?php 
			 
			 
		 }
		 
		 
		 	// long answer	

		 if($question['question_type']==$this->lang->line('long_answer')){
			 $save_ans="";
			 foreach($saved_answers as $svk => $saved_answer){
				 if($question['qid']==$saved_answer['qid']){
					$save_ans=$saved_answer['q_option'];
				 }
			 }
			 ?>
			 <input type="hidden"  name="question_type[]" id="q_type<?php echo $qk;?>" value="4">
			 <?php
			 ?>
			 
		<div class="op"> 
		<!--<?php echo $this->lang->line('answer');?> <br>
		 <?php echo $this->lang->line('word_counts');?> <span id="char_count<?php echo $qk;?>">0</span>
		 <br/>
		  -->

		<strong>Language ID</strong>&nbsp
		<select id="languageId">
			<option value="4">C</option>
			<option value="27">Java</option>
			<option value="10">C++</option>
		</select> 
		<textarea class="code" name="answer[<?php echo $qk;?>][]" id="answer_value<?php echo $qk;?>" style="margin-top:10px;width:100%;height:25%;" onKeyup="count_char(this.value,'char_count<?php echo $qk;?>');"><?php echo $save_ans;?></textarea>
		<div id="panel" style="margin-top:20px; bottom: 20px; right: 20px;">
			<button type="button" id="run">Run</button>
			<button type="button" id="stop" disabled>Stop</button>
			<button type="button" id="clearLog">Clear Log</button>
			<button type="button" id="backToTop">Back To Top</button>
		</div>
		<h5>Request/Response Log</h5><br>
  		<pre id="log"></pre>
		<div style="display:none;">
			<strong>API URL</strong>&nbsp
			<input type="url" name="apiUrl" size="31" value="https://api.judge0.com"><br><br>
			<strong>AUTHENTICATION HEADER</strong>&nbsp
			<input type="text" name="authnHeader" size="31" placeholder="X-Auth-Token" value="X-Auth-Token"><br><br>
			<strong>AUTHENTICATION TOKEN</strong>&nbsp
			<input type="text" size="31" name="authnToken"><br><br>
			<strong>AUTHORIZATION HEADER</strong>&nbsp
			<input type="text" name="authzHeader" size="31" placeholder="X-Auth-User" value="X-Auth-User"><br><br>
			<strong>AUTHORIZATION TOKEN</strong>&nbsp
			<input type="text" size="31" name="authzToken"><br><br>
			<strong>Source Code</strong>
			<input type="checkbox" name="sourceCodeIsNull"><code>null</code><br>
		</div>
		<div style="display:none;">
			<input type="checkbox" name="languageIdIsNull"><code>null</code><br><br>
			<strong>Number Of Runs</strong>&nbsp
			<input type="text" name="numberOfRuns" value="1">
			<input type="checkbox" name="numberOfRunsIsNull"><code>null</code><br><br>

			<strong>CPU Time Limit</strong>&nbsp
			<input type="text" name="cpuTimeLimit" value="2">
			<input type="checkbox" name="cpuTimeLimitIsNull"><code>null</code><br><br>

			<strong>CPU Extra Time</strong>&nbsp
			<input type="text" name="cpuExtraTime" value="0.5">
			<input type="checkbox" name="cpuExtraTimeIsNull"><code>null</code><br><br>

			<strong>Wall Time Limit</strong>&nbsp
			<input type="text" name="wallTimeLimit" value="5">
			<input type="checkbox" name="wallTimeLimitIsNull"><code>null</code><br><br>

			<strong>Memory Limit</strong>&nbsp
			<input type="text" name="memoryLimit" value="128000">
			<input type="checkbox" name="memoryLimitIsNull"><code>null</code><br><br>

			<strong>Stack Limit</strong>&nbsp
			<input type="text" name="stackLimit" value="64000">
			<input type="checkbox" name="stackLimitIsNull"><code>null</code><br><br>

			<strong>Max Processes And Or Threads</strong>&nbsp
			<input type="text" name="maxProcessesAndOrThreads" value="30">
			<input type="checkbox" name="maxProcessesAndOrThreadsIsNull"><code>null</code><br><br>

			<strong>Enable Per Process And Thread Time Limit</strong>
			<input type="radio" name="enablePerProcessAndThreadTimeLimit" value="true"> <code>true</code>
			<input type="radio" name="enablePerProcessAndThreadTimeLimit" value="false" checked> <code>false</code>
			<input type="radio" name="enablePerProcessAndThreadTimeLimit" value="null"> <code>null</code><br><br>

			<strong>Enable Per Process And Thread Memory Limit</strong>
			<input type="radio" name="enablePerProcessAndThreadMemoryLimit" value="true" checked> <code>true</code>
			<input type="radio" name="enablePerProcessAndThreadMemoryLimit" value="false"> <code>false</code>
			<input type="radio" name="enablePerProcessAndThreadMemoryLimit" value="null"> <code>null</code><br><br>

			<strong>Max File Size</strong>&nbsp
			<input type="text" name="maxFileSize" value="1024">
			<input type="checkbox" name="maxFileSizeIsNull"><code>null</code><br><br>

			<strong>Stdin</strong>
			<input type="checkbox" name="stdinIsNull"><code>null</code><br>
			<textarea name="stdin" rows="5" cols="25">Judge0</textarea><br><br>
			
			<input type="checkbox" name="expectedOutputIsNull"><code>null</code><br>

			<strong>Fields</strong>&nbsp
			<input type="text" size="50" name="fields"><br><br>

			<input type="checkbox" name="waitResponse" checked>
			<strong>Wait for submission</strong><br><br>

			<input type="checkbox" name="base64EncodedRequest">
			<strong>Send request with Base64 encoded data</strong><br><br>

			<input type="checkbox" name="base64EncodedResponse">
			<strong>Accept response with Base64 encoded data</strong><br><br>
		</div>
		</div>
			 
			 
			 <?php 
			 
			 
		 }
			 
		
		
		
		
		
		
		// matching	

		 if($question['question_type']==$this->lang->line('match_the_column')){
			 			 			 $save_ans=array();
			 foreach($saved_answers as $svk => $saved_answer){
				 if($question['qid']==$saved_answer['qid']){
					// $exp_match=explode('__',$saved_answer['q_option_match']);
					$save_ans[]=$saved_answer['q_option'];
				 }
			 }
			 
			 
			 ?>
			 <input type="hidden" name="question_type[]" id="q_type<?php echo $qk;?>" value="5">
			 <?php
			$i=0;
			$match_1=array();
			$match_2=array();
			foreach($options as $ok => $option){
				if($option['qid']==$question['qid']){
					$match_1[]=$option['q_option'];
					$match_2[]=$option['q_option_match'];
			?>
			 
			 
			 
			 <?php 
			 $i+=1;
				}else{
				$i=0;	
					
				}
			}
			?>
			<div class="op">
						<table>
						
						<?php 
			shuffle($match_1);
			shuffle($match_2);
			foreach($match_1 as $mk1 =>$mval){
						?>
						<tr><td>
						<?php echo $abc[$mk1];?>)  <?php echo $mval;?> 
						</td><td>
						
							<select name="answer[<?php echo $qk;?>][]" id="answer_value<?php echo $qk.'-'.$mk1;?>"  >
							<option value="0"><?php echo $this->lang->line('select');?></option>
							<?php 
							foreach($match_2 as $mk2 =>$mval2){
								?>
								<option value="<?php echo $mval.'___'.$mval2;?>"  <?php $m1=$mval.'___'.$mval2; if(in_array($m1,$save_ans)){ echo 'selected'; } ?> ><?php echo $mval2;?></option>
								<?php 
							}
							?>
							</select>

						</td>
						</tr>
				
						
						<?php 
			}
			
			
			?>
			</table>
			 </div>
			<?php
			
		 }
			
		 ?>

		</div> 
 </div>
 
 
 
 <?php
}
?>
</form>
 </div>
  <div class="col-md-4" style="padding-bottom:80px;">

<b> <?php echo $this->lang->line('questions');?></b>
	<div style="overflow-y:scroll;height:20%;">
		<?php 
		for($j=0; $j < $quiz['noq']; $j++ ){
			?>
			
			<div class="qbtn" onClick="javascript:show_question('<?php echo $j;?>');" id="qbtn<?php echo $j;?>"  ><?php echo ($j+1);?></div>
			
			<?php 
		}
		?>
<div style="clear:both;"></div>

	</div>
	
	
	<br>
	<hr>
	<br>
	<div>
	

	
<table>
<tr>
	<td style="font-size:12px;line-height:3"><div class="qbtn" style="background:#449d44;">&nbsp;</div> <?php echo $this->lang->line('Answered');?>  </td>
	<td style="font-size:12px;line-height:3"><div class="qbtn" style="background:#c9302c;">&nbsp;</div> <?php echo $this->lang->line('UnAnswered');?>  </td>
</tr>
<tr>
	<td style="font-size:12px;line-height:3"><div class="qbtn" style="background:#ec971f;">&nbsp;</div> <?php echo $this->lang->line('Review-Later');?>  </td>
	<td style="font-size:12px;line-height:3"><div class="qbtn" style="background:#212121;">&nbsp;</div> <?php echo $this->lang->line('Not-visited');?>  </td>
</tr>
</table>



	<div style="clear:both;"></div>

	</div>

 </div>
 
 
 </div>
  
 



</div>



<div class="footer_buttons_simple">
	<button class="btn btn-warning"   onClick="javascript:review_later();" style="margin-top:2px;" ><?php echo $this->lang->line('review_later');?></button>
	
	<button class="btn btn-info"  onClick="javascript:clear_response();"  style="margin-top:2px;"  ><?php echo $this->lang->line('clear');?></button>

	<button class="btn btn-success"  id="backbtn" style="visibility:hidden;" onClick="javascript:show_back_question();"  style="margin-top:2px;" ><?php echo $this->lang->line('back');?></button>
	
	<button class="btn btn-success" id="nextbtn" onClick="javascript:show_next_question();" style="margin-top:2px;" ><?php echo $this->lang->line('save_next');?></button>
	
	<button class="btn btn-danger"  onClick="javascript:cancelmove();" style="margin-top:2px;" ><?php echo $this->lang->line('submit_quiz');?></button>
</div>

<script>
var ctime=0;
var ind_time=new Array();
<?php 
$ind_time=explode(',',$quiz['individual_time']);
for($ct=0; $ct < $quiz['noq']; $ct++){
	?>
ind_time[<?php echo $ct;?>]=<?php if(!isset($ind_time[$ct])){ echo 0;}else{ echo $ind_time[$ct]; }?>;
	<?php 
}
?>
noq="<?php echo $quiz['noq'];?>";
show_question('0');


function increasectime(){
	
	ctime+=1;
 
}
 setInterval(increasectime,1000);
 setInterval(setIndividual_time,30000);
 
</script>
 
 
 
 
 
<div  id="warning_div" style="padding:10px; position:fixed;z-index:100;display:none;width:100%;border-radius:5px;height:200px; border:1px solid #dddddd;left:4px;top:70px;background:#ffffff;">
<center><b> <?php echo $this->lang->line('really_Want_to_submit');?></b> <br><br>
<span id="processing"></span>

<a href="javascript:cancelmove();"   class="btn btn-danger"  style="cursor:pointer;"><?php echo $this->lang->line('cancel');?></a> &nbsp; &nbsp; &nbsp; &nbsp;
<a href="javascript:submit_quiz();"   class="btn btn-info"  style="cursor:pointer;"><?php echo $this->lang->line('submit_quiz');?></a>

</center>
</div>
