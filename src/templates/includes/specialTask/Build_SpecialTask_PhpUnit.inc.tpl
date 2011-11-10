{capture name="specialTaskLink"}<li><a href="#junitReport">Unit tests</a></li><li><a href="#coverageReport">Code coverage</a></li>{/capture}
{capture name="specialTaskPane"}
      <div id="junitReport">
{if !empty($project_buildJunit)}
{$testChartsJs=[]}
{$testChartsHtml=[]}
{foreach from=$project_buildJunit item=classTest}
  {foreach from=$classTest->getTestMethods() item=testMethod}
    {$methodName=$testMethod->getName()}
    {*$totalTests=$testMethod->getTests()}
    {$failedTotal=$testMethod->getFailures()}
    {$okTotal=$totalTests-$failedTotal*}
    {$ok=$testMethod->getCalculatedOks()}
    {$failed=$testMethod->getCalculatedFaileds()}
    {if $testMethod@first}
      {$methodNames="['$methodName'"}
      {$oks="[$ok"}
      {$faileds="[$failed"}
      {*$okTotals="[$okTotal"}
      {$failedTotals="[$failedTotal"*}
    {else if $testMethod@last}
      {$methodNames="$methodNames, '$methodName']"}
      {$oks="$oks, $ok]"}
      {$faileds="$faileds, $failed]"}
      {*$okTotals="$okTotals, $okTotal]"}
      {$failedTotals="$failedTotals, $failedTotal]"*}
    {else}
      {$methodNames="$methodNames, '$methodName'"}
      {$oks="$oks, $ok"}
      {$faileds="$faileds, $failed"}
      {*$okTotals="$okTotals, $okTotal"}
      {$failedTotals="$failedTotals, $failedTotal"*}
    {/if}
  {/foreach}
  {capture append="testChartsJs"}
  {$height=120+32*{$testMethod@total}}
  var chart{$classTest->getName()} = new CintientHighcharts();
  chart{$classTest->getName()}.unitTestChart({
    categories: {$methodNames},
    renderTo: 'chartUnitTests{$classTest->getName()}Container',
    title: '{$classTest->getName()}',
    height: {$height},
    okData: {$oks},
    failedData: {$faileds},
    //okTotal: {*$okTotals*},
    //failedTotal: {*$failedTotals*},
    backgroundColor: {
      linearGradient: [0, 0, 0, {$height}],
      stops: [
        [0.16, '#fff'],
        [0.9, '#eee']
      ]
    }
  });
  {/capture}
  {capture append="testChartsHtml"}
          <li><div id="chartUnitTests{$classTest->getName()}Container" class="chart"></div></li>
  {/capture}
{/foreach}
<ul class="media-grid">{foreach $testChartsHtml as $html}{$html}{/foreach}</ul>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
{foreach $testChartsJs as $js}{$js}{/foreach}
});
//-->
</script>
{else}
The unit tests chart could not be generated. Please check the raw output of the build for problems, e.g., a PHP Fatal error.
{/if}
      </div>
      <div id="coverageReport">
{if !empty($project_buildJunit)}
<iframe id="ccFrame" src="{UrlManager::getForAsset('index.html', ['bid' => $project_build->getId(), 'cc' => true])}" width="930" height="800" seamless></iframe>
{else}
Due to a build error, the unit tests chart could not be generated. Please check the raw output of the build for problems, such as a PHP Fatal error.
{/if}
      </div>
{/capture}