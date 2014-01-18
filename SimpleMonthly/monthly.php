<?php 
/*

All Emoncms code is released under the GNU Affero General Public License.
See COPYRIGHT.txt and LICENSE.txt.

---------------------------------------------------------------------
Emoncms - open source energy visualisation
Part of the OpenEnergyMonitor project:
http://openenergymonitor.org

*/

global $path; 

$sm = $path."Modules/openbem/SimpleMonthly/";

?>

<script type="text/javascript" src="<?php echo $sm; ?>interface/openbem.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>datasets/datasets.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>datasets/element_library.js"></script>

<script type="text/javascript" src="<?php echo $path; ?>Modules/openbem/SimpleMonthly/model/solar.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/openbem/SimpleMonthly/model/windowgains.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/openbem/SimpleMonthly/model/utilisationfactor.js"></script>


<script type="text/javascript" src="<?php echo $sm; ?>controller.js"></script>

<script type="text/javascript" src="<?php echo $sm; ?>Modules/context/context_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/elements/elements_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/ventilation/ventilation_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/waterheating/waterheating_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/solarhotwater/solarhotwater_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/LAC/LAC_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/meaninternaltemperature/meaninternaltemperature_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/balance/balance_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/energyrequirements/energyrequirements_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/fuelcosts/fuelcosts_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/saprating/saprating_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/data/data_model.js"></script>
<script type="text/javascript" src="<?php echo $sm; ?>Modules/measures/measures_model.js"></script>
<ul class="nav nav-pills">
  <li class="active"><a href="#">Simple Monthly</a></li>
  </li>
  <li>
  <a href="<?php echo $path; ?>openbem/dynamic/<?php echo $building; ?>">Dynamic Coheating</a>
  </li>
  <li>
  <a href="<?php echo $path; ?>openbem/heatingexplorer">Heating Explorer</a>
  </li>
</ul>

<div class="row">



  <div class="span3">

    <h3>OpenBEM</h3>

    <canvas id="rating" width="269px" height="350px"></canvas>
    <br><br>
    <table class="table table-bordered">
    <tr><td><a class="menu" name="context">Floor Area and Volume</a></td></tr>
    <tr><td><a class="menu" name="elements">Building Fabric</a></td></tr>
    <tr><td><a class="menu" name="ventilation">Ventilation & Infiltration</a></td></tr>
    <tr><td><a class="menu" name="meaninternaltemperature">Internal Temperature</a></td></tr>
    <tr><td><a class="menu" name="balance">Heat balance</a></td></tr>
    <tr><td><a class="menu" name="energyrequirements">Energy Requirements</a></td></tr>
    <tr><td><a class="menu" name="fuelcosts">Fuel costs</a></td></tr>
    <tr><td><a class="menu" name="saprating">SAP rating</a></td></tr>
    <tr><td><a class="menu" name="data">Export data</a></td></tr>
    <tr><td><a href="<?php echo $path; ?>openbem/measures/<?php echo $building; ?>"><b>Retrofit explorer</b></a></td></tr>
    </table>
    
    <h4>Optional modules</h4>
    <table class="table table-bordered">
    <tr><td><a class="menu" name="waterheating">SAP Water Heating gains</a></td></tr>
    <tr><td><a class="menu" name="solarhotwater">SAP Solar Hot Water gains</a></td></tr>
    <tr><td><a class="menu" name="LAC">SAP Lighting, Appliances<br>& Cooking gains</a></td></tr>
    </table>
    
    
  </div>

  <div class="span9">
    <div id="placeholder" ></div>
  </div>

</div>

<script>
  var c=document.getElementById("rating");
  var ctx=c.getContext("2d");
  
  var path = "<?php echo $path; ?>";
  
  var building = <?php echo $building; ?>;
  var inputdata = openbem.get(building);
  
  if (!inputdata) {
  
    inputdata = {};
  
    inputdata.occupancy = 2;
    inputdata.region = 0;
    inputdata.TFA = 35;
    inputdata.volume = 70;
    inputdata.altitude = 0;
    inputdata.MIT = [21,21,21, 21,21,21, 21,21,21, 21,21,21];
    inputdata.gains = {};
    inputdata.losses = {};

    inputdata.LAC_enabled = false;
    inputdata.solarhotwater_enabled = false;
    inputdata.waterheating_enabled = false;
  }
  
  var i = {}; var o = {};
  
  load_module('balance');
  
  $(".menu").click(function()
  { 
    var module = $(this).attr('name');
    load_module(module);
  });
  
  function load_module(module)
  { 
    if (module=='LAC') inputdata.LAC_enabled = true;
    if (module=='solarhotwater') inputdata.solarhotwater_enabled = true;
    if (module=='waterheating') inputdata.waterheating_enabled = true;
    calc_all();
   
    i = inputdata[module].input;
    o = inputdata[module].output;
    
    $("#placeholder").html(load_view(module));
    openbem_controller(module);
    var customcontroller = module+"_customcontroller";
    if (window[customcontroller]!=undefined) window[customcontroller](module);
    
    openbem_update_view(i,o);
    var customview = module+"_customview";
    if (window[customview]!=undefined) window[customview](i); 
    
    draw_rating(ctx);
    openbem.save(building,inputdata); 
  }
  
  function openbem_update(module)
  { 
    calc_all();
    
    o = inputdata[module].output;
    
    openbem_update_view(i,o);
    
    var customview = module+"_customview";
    if (window[customview]!=undefined) window[customview](i);
    
    draw_rating(ctx);
    
    openbem.save(building,inputdata);
  }
  
  function calc_all()
  {
    calc_module('measures');
    calc_module('context');
    calc_module('ventilation');
    calc_module('elements');
    calc_module('meaninternaltemperature');
    if (inputdata.LAC_enabled) calc_module('LAC');
    if (inputdata.solarhotwater_enabled) calc_module('solarhotwater');
    if (inputdata.waterheating_enabled) calc_module('waterheating');
    calc_module('balance');
    calc_module('energyrequirements');
    calc_module('fuelcosts');
    calc_module('saprating');
    calc_module('data');
    
  }
  
  function calc_module(module)
  {
    var modelname = module+"_model";
    var savetoinputdata = module+"_savetoinputdata";
    
    window[modelname].set_from_inputdata(inputdata);
    inputdata[module] = {
      input:window[modelname].input, 
      output:window[modelname].calc()
    };
    
    if (window[savetoinputdata]!=undefined) window[savetoinputdata](inputdata,inputdata[module].output); 
  }
  

  function load_view(view)
  {
    var result = ""; 
    $.ajax({url: path+"Modules/openbem/SimpleMonthly/Modules/"+view+"/"+view+"_view.html", async: false, cache: false, success: function(data) {result = data;} });
    return result;
  }
  
  function draw_rating(ctx)
  {
    var sap_rating = "?";
    var kwhm2 = "?";
    var letter = "";
    
    var kwhd = 0;
    var kwhdpp = 0;
    
    if (inputdata.saprating!=undefined) {
      sap_rating = Math.round(inputdata.saprating.output.sap_rating);
      var band = 0;
      for (z in ratings)
      {
        if (sap_rating>=ratings[z].start && sap_rating<=ratings[z].end) {band = z; break;}
      }
      color = ratings[band].color;
      letter = ratings[band].letter;
      sap_rating = sap_rating;
    }
    
    if (inputdata.energyrequirements!=undefined) {
      kwhm2 = inputdata.energyrequirements.output.total_energy_requirement_m2;
      kwhm2 = kwhm2.toFixed(0)+" kWh/m2";
      
      kwhd = inputdata.energyrequirements.output.total_energy_requirement / 365.0;
      kwhd = kwhd.toFixed(1)+" kWh/d";

      kwhdpp = inputdata.energyrequirements.output.total_energy_requirement / (365.0 * inputdata.occupancy);
      kwhdpp = kwhdpp.toFixed(1)+" kWh/d";
    }
    
    ctx.clearRect(0,0,269,350);
    
    ctx.fillStyle = color;
    ctx.strokeStyle = color;
    ctx.lineWidth = 3;
    ctx.fillRect(0,0,269,350);

    ctx.fillStyle = "rgba(255,255,255,0.6)";
    ctx.fillRect(0,0,269,350);
    ctx.strokeRect(0,0,269,350);
        
    var mid = 269 / 2;
    
    ctx.beginPath();
    ctx.arc(mid, mid, 100, 0, 2 * Math.PI, false);
    ctx.closePath();
    ctx.fillStyle = "rgba(255,255,255,0.6)";
    ctx.fill();
    ctx.stroke();
    
    ctx.fillStyle = color;
      
    ctx.textAlign = "center";
    
    ctx.font = "bold 22px arial";
    ctx.fillText("SAP",mid,90);  
    ctx.font = "bold 92px arial";
      
    ctx.fillText(sap_rating,mid,mid+30);

    ctx.font = "bold 22px arial";
    ctx.fillText(letter+" RATING",mid,mid+60);    
    ctx.font = "bold 32px arial";
    
    //ctx.shadowColor = "rgba(0,0,0,0.0)";
    //ctx.shadowOffsetX = 1; 
    //ctx.shadowOffsetY = 1; 
    //ctx.shadowBlur = 3;
    ctx.fillText(kwhm2,mid,280);
    
    ctx.font = "bold 18px arial";
    ctx.fillText("DAILY: "+kwhd,mid,308);

    ctx.font = "bold 18px arial";
    ctx.fillText("PER PERSON: "+kwhdpp,mid,336);
  }
  
</script>