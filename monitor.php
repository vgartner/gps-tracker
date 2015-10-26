<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Documento sem título</title>
<link rel="stylesheet" href="/extjs/resources/css/ext-all-neptune.css">
<script type="text/javascript" src="http://maps.google.com/maps/api/js?v=3.exp&sensor=true"></script>
<script src="/extjs/ext-all.js"></script>
</head>

<body>
<script type="text/javascript">
Ext.create('Ext.container.Viewport', {
    layout: 'border',
    items: [{
        region: 'north',
        html: '<h1 class="x-panel-header">Page Title</h1>',
        border: false,
        margins: '0 0 5 0'
    }, {
        region: 'west',
        collapsible: true,
        title: 'Navigation',
        width: 150
        // could use a TreePanel or AccordionLayout for navigational items
    }, {
        region: 'south',
        title: 'South Panel',
        collapsible: true,
        html: 'Information goes here',
        split: true,
        height: 100,
        minHeight: 100
    }, {
        region: 'center',
        xtype: 'tabpanel', // TabPanel itself has no title
        activeTab: 0,      // First tab active by defaultc	
        items: {
            title: 'Default Tab',
            html: '<div id="map_canvas" style="width:1000px; height:100%"></div>'
        }
    }]
});

function initialize() {
	var latlng = new google.maps.LatLng(-13.496473,-55.722656);
	var myOptions = {
		zoom: 4,
		center: latlng,
		navigationControl: true,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	
	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
}

initialize();
</script>

</body>
</html>