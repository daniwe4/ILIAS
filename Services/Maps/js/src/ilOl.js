import ServiceOpenLayers from './ServiceOpenLayers';

initIlOpenLayerMaps = function(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers) {
	var ol = new ServiceOpenLayers(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);
	return ol;
};

ilLookupAddress = function(id, address) {
    return openLayer.jumpToAddress(id, address);
};
ilUpdateMap = function (id) {
    return openLayer.updateMap(id);
};
ilShowUserMarker = function(id, counter) {
    return openLayer.moveToUserMarkerAndOpen(id, counter);
};