angular.module('openITCOCKPIT')
    .controller('ServicesBrowserController', function($scope, $http, $q, QueryStringService, $interval){

        $scope.id = QueryStringService.getCakeId();

        $scope.showFlashSuccess = false;

        $scope.canSubmitExternalCommands = false;

        $scope.tags = [];

        $scope.init = true;

        $scope.serviceStatusTextClass = 'txt-primary';

        $scope.isLoadingGraph = false;

        $scope.dataSources = [];
        $scope.currentDataSource = null;

        $scope.serverTimeDateObject = null;

        $scope.availableTimeranges = {
            1: '1 hour',
            2: '2 hours',
            3: '3 hours',
            4: '4 hours',
            8: '8 hours',
            24: '1 day',
            48: '2 days',
            120: '5 days',
            168: '7 days',
            720: '30 days',
            2160: '90 days',
            4464: '6 months',
            8760: '1 year'
        };
        $scope.currentSelectedTimerange = 3;

        $scope.visTimeline = null;
        $scope.visTimelineInit = true;
        $scope.visTimelineStart = -1;
        $scope.visTimelineEnd = -1;
        $scope.visTimeout = null;
        $scope.visChangeTimeout = null;
        $scope.showTimelineTab = false;
        $scope.timelineIsLoading = false;
        $scope.failureDurationInPercent = null;
        $scope.lastLoadDate = Date.now();

        $scope.graphAutoRefresh = true;
        $scope.graphAutoRefreshInterval = 0;
        $scope.showDatapoints = false;

        var flappingInterval;
        var zoomCallbackWasBind = false;
        var graphAutoRefreshIntervalId = null;
        var lastGraphStart = 0;
        var lastGraphEnd = 0;
        var graphRenderEnd = 0;

        $scope.showFlashMsg = function(){
            $scope.showFlashSuccess = true;
            $scope.autoRefreshCounter = 5;
            var interval = $interval(function(){
                $scope.autoRefreshCounter--;
                if($scope.autoRefreshCounter === 0){
                    $scope.load();
                    $interval.cancel(interval);
                    $scope.showFlashSuccess = false;
                }
            }, 1000);
        };

        $scope.load = function(){
            $scope.lastLoadDate = Date.now();
            $q.all([
                $http.get("/services/browser/" + $scope.id + ".json", {
                    params: {
                        'angular': true
                    }
                }),
                $http.get("/angular/user_timezone.json", {
                    params: {
                        'angular': true
                    }
                })
            ]).then(function(results){
                $scope.mergedService = results[0].data.mergedService;
                $scope.mergedService.Service.disabled = parseInt($scope.mergedService.Service.disabled, 10);
                $scope.contacts = results[0].data.contacts;
                $scope.contactgroups = results[0].data.contactgroups;
                $scope.host = results[0].data.host;
                $scope.tags = $scope.mergedService.Service.tags.split(',');
                $scope.hoststatus = results[0].data.hoststatus;
                $scope.servicestatus = results[0].data.servicestatus;
                $scope.servicestatusForIcon = {
                    Servicestatus: $scope.servicestatus
                };
                $scope.serviceStatusTextClass = getServicestatusTextColor();


                $scope.acknowledgement = results[0].data.acknowledgement;
                $scope.downtime = results[0].data.downtime;

                $scope.hostAcknowledgement = results[0].data.hostAcknowledgement;
                $scope.hostDowntime = results[0].data.hostDowntime;

                $scope.canSubmitExternalCommands = results[0].data.canSubmitExternalCommands;

                $scope.priorities = {
                    1: false,
                    2: false,
                    3: false,
                    4: false,
                    5: false
                };
                var priority = parseInt($scope.mergedService.Service.priority, 10);
                for(var i = 1; i <= priority; i++){
                    $scope.priorities[i] = true;
                }

                $scope.graphAutoRefreshInterval = parseInt($scope.mergedService.Service.check_interval, 10) * 1000;
                $scope.timezone = results[1].data.timezone;

                $scope.serverTimeDateObject = new Date($scope.timezone.server_time);
                //var graphStart = (parseInt(new Date().getTime() / 1000, 10) - (3 * 3600));
                //var graphEnd = parseInt(new Date().getTime() / 1000, 10);

                var graphStart = (parseInt($scope.serverTimeDateObject.getTime() / 1000, 10) - (3 * 3600));
                var graphEnd = parseInt($scope.serverTimeDateObject.getTime() / 1000, 10);

                $scope.dataSources = [];
                for(var dsName in results[0].data.mergedService.Perfdata){
                    $scope.dataSources.push(dsName);
                }
                if($scope.dataSources.length > 0){
                    $scope.currentDataSource = $scope.dataSources[0];
                }


                if($scope.mergedService.Service.has_graph){
                    loadGraph($scope.host.Host.uuid, $scope.mergedService.Service.uuid, false, graphStart, graphEnd, true);
                }

                $scope.init = false;
            });
        };

        $scope.loadTimezone = function(){
            $http.get("/angular/user_timezone.json", {
                params: {
                    'angular': true
                }
            }).then(function(result){
                $scope.timezone = result.data.timezone;
            });
        };


        $scope.getObjectForDowntimeDelete = function(){
            var object = {};
            object[$scope.downtime.internalDowntimeId] = $scope.host.Host.name + ' / ' + $scope.mergedService.Service.name;
            return object;
        };

        $scope.getObjectForHostDowntimeDelete = function(){
            var object = {};
            object[$scope.hostDowntime.internalDowntimeId] = $scope.host.Host.name;
            return object;
        };

        $scope.getObjectsForExternalCommand = function(){
            return [{
                Service: {
                    id: $scope.mergedService.Service.id,
                    uuid: $scope.mergedService.Service.uuid,
                    name: $scope.mergedService.Service.name
                },
                Host: {
                    id: $scope.host.Host.id,
                    uuid: $scope.host.Host.uuid,
                    name: $scope.host.Host.name,
                    satelliteId: $scope.host.Host.satellite_id
                }
            }];
        };


        $scope.stateIsOk = function(){
            return parseInt($scope.servicestatus.currentState, 10) === 0;
        };

        $scope.stateIsWarning = function(){
            return parseInt($scope.servicestatus.currentState, 10) === 1;
        };

        $scope.stateIsCritical = function(){
            return parseInt($scope.servicestatus.currentState, 10) === 2;
        };

        $scope.stateIsUnknown = function(){
            return parseInt($scope.servicestatus.currentState, 10) === 3;
        };

        $scope.stateIsNotInMonitoring = function(){
            return !$scope.servicestatus.isInMonitoring;
        };

        $scope.startFlapping = function(){
            $scope.stopFlapping();
            flappingInterval = $interval(function(){
                if($scope.flappingState === 0){
                    $scope.flappingState = 1;
                }else{
                    $scope.flappingState = 0;
                }
            }, 750);
        };

        $scope.stopFlapping = function(){
            if(flappingInterval){
                $interval.cancel(flappingInterval);
            }
            flappingInterval = null;
        };

        $scope.changeGraphTimespan = function(timespan){
            $scope.currentSelectedTimerange = timespan;
            var start = (parseInt(new Date($scope.timezone.server_time).getTime() / 1000, 10) - (timespan * 3600));
            var end = parseInt(new Date($scope.timezone.server_time).getTime() / 1000, 10);
            //graphTimeSpan = timespan;
            loadGraph($scope.host.Host.uuid, $scope.mergedService.Service.uuid, false, start, end, true);
        };

        $scope.changeDataSource = function(gaugeName){
            $scope.currentDataSource = gaugeName;
            loadGraph($scope.host.Host.uuid, $scope.mergedService.Service.uuid, false, lastGraphStart, lastGraphEnd, false);
        };

        var getServicestatusTextColor = function(){
            switch($scope.servicestatus.currentState){
                case 0:
                case '0':
                    return 'txt-color-green';

                case 1:
                case '1':
                    return 'warning';

                case 2:
                case '2':
                    return 'txt-color-red';

                case 3:
                case '3':
                    return 'txt-color-blueLight';
            }
            return 'txt-primary';
        };


        var loadGraph = function(hostUuid, serviceuuid, appendData, start, end, saveStartAndEnd){
            if(saveStartAndEnd){
                lastGraphStart = start;
                lastGraphEnd = end;
            }

            //The last timestamp in the y-axe
            graphRenderEnd = end;

            if($scope.dataSources.length > 0){
                $scope.isLoadingGraph = true;
                $http.get('/Graphgenerators/getPerfdataByUuid.json', {
                    params: {
                        angular: true,
                        host_uuid: hostUuid,
                        service_uuid: serviceuuid,
                        //hours: graphTimeSpan,
                        start: start,
                        end: end,
                        jsTimestamp: 1,
                        gauge: $scope.currentDataSource
                    }
                }).then(function(result){
                    $scope.isLoadingGraph = false;
                    if(appendData === false){
                        //Did we got date from Server?
                        if(result.data.performance_data.length > 0){
                            //Use the first metrics the server gave us.
                            $scope.perfdata = result.data.performance_data[0];
                        }else{
                            $scope.perfdata = {
                                data: {},
                                datasource: {
                                    ds: null,
                                    name: null,
                                    label: null,
                                    unit: null,
                                    act: null,
                                    warn: null,
                                    crit: null,
                                    min: null,
                                    max: null
                                }
                            };
                        }
                    }

                    if(appendData === true){
                        if(result.data.performance_data.length > 0){
                            //Append new data to current graph
                            for(var timestamp in result.data.performance_data[0].data){
                                var frontEndTimestamp = (parseInt(timestamp, 10) + ($scope.timezone.user_offset * 1000));
                                $scope.perfdata.data[frontEndTimestamp] = result.data.performance_data[0].data[timestamp];
                            }
                        }
                    }
                    if($scope.graphAutoRefresh === true && $scope.graphAutoRefreshInterval > 1000){
                        enableGraphAutorefresh();
                    }
                    renderGraph($scope.perfdata);
                });
            }
        };

        var initTooltip = function(){
            var previousPoint = null;
            var $graph_data_tooltip = $('#graph_data_tooltip');

            $graph_data_tooltip.css({
                position: 'absolute',
                display: 'none',
                //border: '1px solid #666',
                'border-top-left-radius': '5px',
                'border-top-right-radius': '0',
                'border-bottom-left-radius': '0',
                'border-bottom-right-radius': '5px',
                padding: '2px 4px',
                'background-color': '#f2f2f2',
                'border-radius': '5px',
                opacity: 0.9,
                'box-shadow': '2px 2px 3px #888',
                transition: 'all 1s'
            });

            $('#graphCanvas').bind('plothover', function(event, pos, item){
                $('#x').text(pos.pageX.toFixed(2));
                $('#y').text(pos.pageY.toFixed(2));

                if(item){
                    if(previousPoint != item.dataIndex){
                        previousPoint = item.dataIndex;

                        $('#graph_data_tooltip').hide();

                        var value = item.datapoint[1];
                        if(!isNaN(value) && isFinite(value)){
                            value = value.toFixed(4);
                        }
                        var tooltip_text = value;
                        if(item.series['unit']){
                            tooltip_text += ' ' + item.series.unit;
                        }

                        showTooltip(item.pageX, item.pageY, tooltip_text, item.datapoint[0]);
                    }
                }else{
                    $("#graph_data_tooltip").hide();
                    previousPoint = null;
                }
            });
        };

        var showTooltip = function(x, y, contents, timestamp){
            var self = this;
            var $graph_data_tooltip = $('#graph_data_tooltip');

            var fooJS = new Date(timestamp);
            var fixTime = function(value){
                if(value < 10){
                    return '0' + value;
                }
                return value;
            };

            var humanTime = fixTime(fooJS.getDate()) + '.' + fixTime(fooJS.getMonth() + 1) + '.' + fooJS.getFullYear() + ' ' + fixTime(fooJS.getHours()) + ':' + fixTime(fooJS.getMinutes());

            $graph_data_tooltip
                .html('<i class="fa fa-clock-o"></i> ' + humanTime + '<br /><strong>' + contents + '</strong>')
                .css({
                    top: y,
                    left: x + 10
                })
                .appendTo('body')
                .fadeIn(200);
        };

        var renderGraph = function(performance_data){
            initTooltip();

            var thresholdLines = [];
            var thresholdAreas = [];

            var GraphDefaultsObj = new GraphDefaults();

            var defaultColor = GraphDefaultsObj.defaultFillColor;

            if(performance_data.datasource.warn !== "" &&
                performance_data.datasource.crit !== "" &&
                performance_data.datasource.warn !== null &&
                performance_data.datasource.crit !== null){

                var warn = parseFloat(performance_data.datasource.warn);
                var crit = parseFloat(performance_data.datasource.crit);

                //Add warning and critical line to chart
                thresholdLines.push({
                    color: GraphDefaultsObj.warningBorderColor,
                    yaxis: {
                        from: warn,
                        to: warn
                    }
                });

                thresholdLines.push({
                    color: GraphDefaultsObj.criticalBorderColor,
                    yaxis: {
                        from: crit,
                        to: crit
                    }
                });

                //Change color of the area chart for warning and critical
                if(warn > crit){
                    thresholdAreas.push({
                        below: warn,
                        color: GraphDefaultsObj.warningFillColor
                    });
                    thresholdAreas.push({
                        below: crit,
                        color: GraphDefaultsObj.criticalFillColor
                    });
                }else{
                    defaultColor = GraphDefaultsObj.criticalFillColor;
                    thresholdAreas.push({
                        below: crit,
                        color: GraphDefaultsObj.warningFillColor
                    });
                    thresholdAreas.push({
                        below: warn,
                        color: GraphDefaultsObj.okFillColor
                    });
                }
            }

            var graph_data = [];
            for(var timestamp in performance_data.data){
                var frontEndTimestamp = (parseInt(timestamp, 10) + ($scope.timezone.user_time_to_server_offset * 1000));
                graph_data.push([frontEndTimestamp, performance_data.data[timestamp]]);
            }

            var options = GraphDefaultsObj.getDefaultOptions();

            options.height = '300';
            options.colors = defaultColor;
            options.tooltip = true;
            options.tooltipOpts = {
                defaultTheme: false
            };
            options.xaxis.tickFormatter = function(val, axis){
                var fooJS = new Date(val);
                var fixTime = function(value){
                    if(value < 10){
                        return '0' + value;
                    }
                    return value;
                };
                return fixTime(fooJS.getDate()) + '.' + fixTime(fooJS.getMonth() + 1) + '.' + fooJS.getFullYear() + ' ' + fixTime(fooJS.getHours()) + ':' + fixTime(fooJS.getMinutes());
            };
            options.series.color = defaultColor;
            options.series.threshold = thresholdAreas;
            options.lines.fillColor.colors = [{opacity: 0.3}, {brightness: 1, opacity: 0.6}];

            options.points = {
                show: $scope.showDatapoints,
                radius: 1
            };

            options.xaxis.min = (lastGraphStart + $scope.timezone.user_time_to_server_offset) * 1000;
            options.xaxis.max = (graphRenderEnd + $scope.timezone.user_time_to_server_offset) * 1000;

            options.yaxis = {
                axisLabel: performance_data.datasource.unit
            };

            plot = $.plot('#graphCanvas', [graph_data], options);

            if(zoomCallbackWasBind === false){
                $("#graphCanvas").bind("plotselected", function(event, ranges){
                    var start = parseInt(ranges.xaxis.from / 1000, 10);
                    var end = parseInt(ranges.xaxis.to / 1000, 10);

                    start -= $scope.timezone.user_time_to_server_offset;
                    end -= $scope.timezone.user_time_to_server_offset;

                    //Zoomed from right to left?
                    if(start > end){
                        var tmpStart = end;
                        end = start;
                        start = tmpStart;
                    }

                    var currentTimestamp = Math.floor($scope.serverTimeDateObject.getTime() / 1000);
                    var graphAutoRefreshIntervalInSeconds = $scope.graphAutoRefreshInterval / 1000;

                    //Only enable autorefresh, if graphEnd timestamp is near to now
                    //We dont need to autorefresh data from yesterday
                    if((end + graphAutoRefreshIntervalInSeconds + 120) < currentTimestamp){
                        disableGraphAutorefresh();
                    }

                    loadGraph($scope.host.Host.uuid, $scope.mergedService.Service.uuid, false, start, end, true);
                });
            }

            zoomCallbackWasBind = true;
        };

        $scope.loadTimelineData = function(_properties){
            var properties = _properties || {};
            var start = properties.start || -1;
            var end = properties.end || -1;

            $scope.timelineIsLoading = true;

            if(start > $scope.visTimelineStart && end < $scope.visTimelineEnd){
                $scope.timelineIsLoading = false;
                //Zoom in data we already have
                return;
            }

            $http.get("/services/timeline/" + $scope.id + ".json", {
                params: {
                    'angular': true,
                    start: start,
                    end: end
                }
            }).then(function(result){
                var timelinedata = {
                    items: new vis.DataSet(result.data.servicestatehistory),
                    groups: new vis.DataSet(result.data.groups)
                };
                timelinedata.items.add(result.data.statehistory);
                timelinedata.items.add(result.data.downtimes);
                timelinedata.items.add(result.data.notifications);
                timelinedata.items.add(result.data.acknowledgements);
                timelinedata.items.add(result.data.timeranges);

                $scope.visTimelineStart = result.data.start;
                $scope.visTimelineEnd = result.data.end;
                var options = {
                    orientation: "both",
                    start: new Date(result.data.start * 1000),
                    end: new Date(result.data.end * 1000),
                    min: new Date(new Date(result.data.start * 1000).setFullYear(new Date(result.data.start * 1000).getFullYear() - 1)), //May 1 year of zoom
                    max: new Date(result.data.end * 1000),    // upper limit of visible range
                    zoomMin: 1000 * 10 * 60 * 5,   // every 5 minutes
                    format: {
                        minorLabels: {
                            millisecond: 'SSS',
                            second: 's',
                            minute: 'H:mm',
                            hour: 'H:mm',
                            weekday: 'ddd D',
                            day: 'D',
                            week: 'w',
                            month: 'MMM',
                            year: 'YYYY'
                        },
                        majorLabels: {
                            millisecond: 'H:mm:ss',
                            second: 'D MMMM H:mm',
                            // minute:     'ddd D MMMM',
                            // hour:       'ddd D MMMM',
                            minute: 'DD.MM.YYYY',
                            hour: 'DD.MM.YYYY',
                            weekday: 'MMMM YYYY',
                            day: 'MMMM YYYY',
                            week: 'MMMM YYYY',
                            month: 'YYYY',
                            year: ''
                        }
                    }
                };
                renderTimeline(timelinedata, options);
                $scope.timelineIsLoading = false;
            });
        };

        var renderTimeline = function(timelinedata, options){
            var container = document.getElementById('visualization');
            if($scope.visTimeline === null){
                $scope.visTimeline = new vis.Timeline(container, timelinedata.items, timelinedata.groups, options);
                $scope.visTimeline.on('rangechanged', function(properties){
                    if($scope.visTimelineInit){
                        $scope.visTimelineInit = false;
                        return;
                    }

                    if($scope.timelineIsLoading){
                        return;
                    }

                    if($scope.visTimeout){
                        clearTimeout($scope.visTimeout);
                    }
                    $scope.visTimeout = setTimeout(function(){
                        $scope.visTimeout = null;
                        $scope.loadTimelineData({
                            start: parseInt(properties.start.getTime() / 1000, 10),
                            end: parseInt(properties.end.getTime() / 1000, 10)
                        });
                    }, 500);
                });
            }else{
                $scope.visTimeline.setItems(timelinedata.items);
            }

            $scope.visTimeline.on('changed', function(){
                if($scope.visTimelineInit){
                    return;
                }
                if($scope.visChangeTimeout){
                    clearTimeout($scope.visChangeTimeout);
                }
                $scope.visChangeTimeout = setTimeout(function(){
                    $scope.visChangeTimeout = null;
                    var timeRange = $scope.visTimeline.getWindow();
                    var visTimelineStartAsTimestamp = new Date(timeRange.start).getTime();
                    var visTimelineEndAsTimestamp = new Date(timeRange.end).getTime();
                    var criticalItems = $scope.visTimeline.itemsData.get({
                        fields: ['start', 'end', 'className', 'group'],    // output the specified fields only
                        type: {
                            start: 'Date',
                            end: 'Date'
                        },
                        filter: function(item){
                            return (item.group == 4 &&
                                (item.className === 'bg-critical' || item.className === 'bg-critical-soft') &&
                                $scope.CheckIfItemInRange(
                                    visTimelineStartAsTimestamp,
                                    visTimelineEndAsTimestamp,
                                    item
                                )
                            );

                        }
                    });
                    $scope.failureDurationInPercent = $scope.calculateFailures(
                        (visTimelineEndAsTimestamp - visTimelineStartAsTimestamp), //visible time range
                        criticalItems,
                        visTimelineStartAsTimestamp,
                        visTimelineEndAsTimestamp
                    );
                    $scope.$apply();
                }, 500);

            });
        };

        $scope.showTimeline = function(){
            $scope.showTimelineTab = true;
            $scope.loadTimelineData();
        };

        $scope.hideTimeline = function(){
            $scope.showTimelineTab = false;
        };

        $scope.CheckIfItemInRange = function(start, end, item){
            var itemStart = item.start.getTime();
            var itemEnd = item.end.getTime();
            if(itemEnd < start){
                return false;
            }else if(itemStart > end){
                return false;
            }else if(itemStart >= start && itemEnd <= end){
                return true;
            }else if(itemStart >= start && itemEnd > end){ //item started behind the start and ended behind the end
                return true;
            }else if(itemStart < start && itemEnd > start && itemEnd < end){ //item started before the start and ended behind the end
                return true;
            }else if(itemStart < start && itemEnd >= end){ // item startet before the start and enden before the end
                return true;
            }
            return false;
        };

        $scope.calculateFailures = function(totalTime, criticalItems, start, end){
            var failuresDuration = 0;

            criticalItems.forEach(function(criticalItem){
                var itemStart = criticalItem.start.getTime();
                var itemEnd = criticalItem.end.getTime();
                failuresDuration += ((itemEnd > end) ? end : itemEnd) - ((itemStart < start) ? start : itemStart);
            });
            return (failuresDuration / totalTime * 100).toFixed(3);
        };

        var enableGraphAutorefresh = function(){
            $scope.graphAutoRefresh = true;

            if(graphAutoRefreshIntervalId === null){
                graphAutoRefreshIntervalId = $interval(function(){
                    //Find last timestamp to only load new data and keep the existing
                    var lastTimestampInCurrentData = 0;
                    for(var timestamp in $scope.perfdata.data){
                        timestamp = parseInt(timestamp, 10);
                        if(timestamp > lastTimestampInCurrentData){
                            lastTimestampInCurrentData = timestamp;
                        }
                    }

                    lastTimestampInCurrentData = lastTimestampInCurrentData / 1000;

                    var start = lastTimestampInCurrentData;
                    $scope.serverTimeDateObject = new Date($scope.serverTimeDateObject.getTime() + $scope.graphAutoRefreshInterval);
                    var end = Math.floor($scope.serverTimeDateObject.getTime() / 1000);
                    if(start > 0){
                        loadGraph($scope.host.Host.uuid, $scope.mergedService.Service.uuid, true, start, end, false);
                    }
                }, $scope.graphAutoRefreshInterval);
            }
        };

        var disableGraphAutorefresh = function(){
            $scope.graphAutoRefresh = false;

            if(graphAutoRefreshIntervalId !== null){
                $interval.cancel(graphAutoRefreshIntervalId);
            }
            graphAutoRefreshIntervalId = null;
        };

        $scope.load();

        $scope.$watch('servicestatus.isFlapping', function(){
            if($scope.servicestatus){
                if($scope.servicestatus.hasOwnProperty('isFlapping')){
                    if($scope.servicestatus.isFlapping === true){
                        $scope.startFlapping();
                    }

                    if($scope.servicestatus.isFlapping === false){
                        $scope.stopFlapping();
                    }

                }
            }
        });

        $scope.$watch('graphAutoRefresh', function(){
            if($scope.init){
                return;
            }

            if($scope.graphAutoRefresh === true){
                enableGraphAutorefresh();
            }else{
                disableGraphAutorefresh();
            }
        });

        $scope.$watch('showDatapoints', function(){
            if($scope.init){
                return;
            }
            loadGraph($scope.host.Host.uuid, $scope.mergedService.Service.uuid, true, lastGraphStart, lastGraphEnd, false);
        });

    });
