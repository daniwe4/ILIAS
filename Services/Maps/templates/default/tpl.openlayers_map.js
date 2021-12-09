let ilOLMapData = [];
let ilOLUserMarkers = [];
let ilOLInvalidAddress = undefined;

ilOLMapData["{MAP_ID}"] = new Array({LAT}, {LONG}, {ZOOM}, {CENTRAL_MARKER}, {NAV_CONTROL}, {REPLACE_MARKER}, {TILES}, "{GEOLOCATION}");
ilOLUserMarkers["{MAP_ID}"] = new Array();

ilOLInvalidAddress = "{INVALID_ADDRESS_STRING}";

<!-- BEGIN user_marker -->
ilOLUserMarkers["{UMAP_ID}"][{CNT}] = new Array({ULONG}, {ULAT},
"<img style='float:right; margin-right:10px; margin-left:10px;' className='ilUserXXSmall' src='{IMG_USER}'\/><span className='small'>{USER_INFO}<\/span>");
<!-- END user_marker -->

let openLayer = new ServiceOpenLayers(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);

ilLookupAddress = function(id, address) {
    return openLayer.jumpToAddress(id, address);
};

ilUpdateMap = function (id) {
    return openLayer.updateMap(id);
};

ilShowUserMarker = function(id, counter) {
    return openLayer.moveToUserMarkerAndOpen(id, counter);
};

openLayer.forceResize(jQuery);
openLayer.init(ilOLMapData);
