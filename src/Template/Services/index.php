<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.
?>
<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item">
        <a ui-sref="DashboardsIndex">
            <i class="fa fa-home"></i> <?php echo __('Home'); ?>
        </a>
    </li>
    <li class="breadcrumb-item">
        <a ui-sref="ServicesIndex">
            <i class="fa fa-cog"></i> <?php echo __('Services'); ?>
        </a>
    </li>
    <li class="breadcrumb-item">
        <i class="fa fa-stethoscope"></i> <?php echo __('Monitored'); ?>
    </li>
</ol>

<!-- ANGAULAR DIRECTIVES -->
<query-handler-directive></query-handler-directive>
<massdelete></massdelete>
<massdeactivate></massdeactivate>
<?php if ($this->Acl->hasPermission('add', 'servicegroups')): ?>
    <add-services-to-servicegroup></add-services-to-servicegroup>
<?php endif; ?>

<div class="alert alert-success alert-block" id="flashSuccess" style="display:none;">
    <a href="javascript:void(0);" data-dismiss="alert" class="close">×</a>
    <h4 class="alert-heading"><i class="fa fa-check-circle"></i> <?php echo __('Command sent successfully'); ?></h4>
    <?php echo __('Page refresh in'); ?>
    <span id="autoRefreshCounter"></span> <?php echo __('seconds...'); ?>
</div>

<div class="row">
    <div class="col-xl-12">
        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    <?php echo __('Services'); ?>
                    <span class="fw-300"><i><?php echo __('overview'); ?></i></span>
                </h2>
                <div class="panel-toolbar">
                    <ul class="nav nav-tabs border-bottom-0 nav-tabs-clean" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" ui-sref="ServicesIndex" role="tab">
                                <i class="fa fa-stethoscope">&nbsp;</i> <?php echo __('Monitored'); ?>
                            </a>
                        </li>
                        <?php if ($this->Acl->hasPermission('notMonitored', 'services')): ?>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" ui-sref="ServicesNotMonitored" role="tab">
                                    <i class="fa fa-user-md">&nbsp;</i> <?php echo __('Not monitored'); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($this->Acl->hasPermission('disabled', 'services')): ?>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" ui-sref="ServicesDisabled" role="tab">
                                    <i class="fa fa-plug">&nbsp;</i> <?php echo __('Disabled'); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <button class="btn btn-xs btn-default mr-1 shadow-0" ng-click="load()">
                        <i class="fas fa-sync"></i> <?php echo __('Refresh'); ?>
                    </button>
                    <?php if ($this->Acl->hasPermission('add', 'services')): ?>
                        <button class="btn btn-xs btn-success mr-1 shadow-0" ui-sref="ServicesAdd">
                            <i class="fas fa-plus"></i> <?php echo __('New'); ?>
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-xs btn-danger mr-1 shadow-0" ng-click="problemsOnly()">
                        <i class="fas fa-plus"></i> <?php echo __('Unhandled only'); ?>
                    </button>
                    <button class="btn btn-xs btn-primary shadow-0" ng-click="triggerFilter()">
                        <i class="fas fa-filter"></i> <?php echo __('Filter'); ?>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">

                    <!-- Start Filter -->
                    <div class="list-filter card margin-bottom-10" ng-show="showFilter">
                        <div class="card-header">
                            <i class="fa fa-filter"></i> <?php echo __('Filter'); ?>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 margin-bottom-10">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-desktop"></i></span>
                                            </div>
                                            <input type="text" class="form-control form-control-sm"
                                                   placeholder="<?php echo __('Filter by host name'); ?>"
                                                   ng-model="filter.Hosts.name"
                                                   ng-model-options="{debounce: 500}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6 margin-bottom-10">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-cog"></i></span>
                                            </div>
                                            <input type="text" class="form-control form-control-sm"
                                                   placeholder="<?php echo __('Filter by service name'); ?>"
                                                   ng-model="filter.Services.name"
                                                   ng-model-options="{debounce: 500}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6 margin-bottom-10">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-filter"></i></span>
                                            </div>
                                            <input type="text" class="form-control form-control-sm"
                                                   placeholder="<?php echo __('Filter by service description'); ?>"
                                                   ng-model="filter.Services.servicedescription"
                                                   ng-model-options="{debounce: 500}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6 margin-bottom-10">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-filter"></i></span>
                                            </div>
                                            <input type="text" class="form-control form-control-sm"
                                                   placeholder="<?php echo __('Filter by output'); ?>"
                                                   ng-model="filter.Servicestatus.output"
                                                   ng-model-options="{debounce: 500}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6 margin-bottom-10">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-filter"></i></span>
                                            </div>
                                            <div class="col tagsinputFilter">
                                                <input type="text"
                                                       class="form-control form-control-sm "
                                                       data-role="tagsinput"
                                                       id="ServicesKeywordsInput"
                                                       placeholder="<?php echo __('Filter by tags'); ?>"
                                                       ng-model="filter.Services.keywords"
                                                       ng-model-options="{debounce: 500}"
                                                       style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-6 margin-bottom-10">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-filter"></i></span>
                                            </div>
                                            <div class="col tagsinputFilter">
                                                <input type="text" class="input-sm"
                                                       data-role="tagsinput"
                                                       id="ServicesNotKeywordsInput"
                                                       placeholder="<?php echo __('Filter by excluded tags'); ?>"
                                                       ng-model="filter.Services.not_keywords"
                                                       ng-model-options="{debounce: 500}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-lg-2">
                                    <fieldset>
                                        <h5><?php echo __('Service status'); ?></h5>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="statusFilterOk"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.current_state.ok"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label custom-control-label-ok"
                                                       for="statusFilterOk"><?php echo __('Ok'); ?></label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="statusFilterWarning"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.current_state.warning"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label custom-control-label-warning"
                                                       for="statusFilterWarning"><?php echo __('Warning'); ?></label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="statusFilterCritical"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.current_state.critical"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label custom-control-label-critical"
                                                       for="statusFilterCritical"><?php echo __('Critical'); ?></label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="statusFilterUnknown"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.current_state.unknown"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label custom-control-label-unknown"
                                                       for="statusFilterUnknown"><?php echo __('Unknown'); ?></label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-xs-12 col-lg-2">
                                    <fieldset>
                                        <h5><?php echo __('Acknowledgements'); ?></h5>
                                        <div class="form-group smart-form">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="ackFilterAck"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.acknowledged"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="ackFilterAck"><?php echo __('Acknowledge'); ?></label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="ackFilterNotAck"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.not_acknowledged"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="ackFilterNotAck"><?php echo __('Not acknowledged'); ?></label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-xs-12 col-lg-2">
                                    <fieldset>
                                        <h5><?php echo __('Downtimes'); ?></h5>
                                        <div class="form-group smart-form">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="downtimwFilterInDowntime"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.in_downtime"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="downtimwFilterInDowntime"><?php echo __('In downtime'); ?></label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="downtimwFilterNotInDowntime"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.not_in_downtime"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="downtimwFilterNotInDowntime"><?php echo __('Not in downtime'); ?></label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-xs-12 col-lg-2">
                                    <fieldset>
                                        <h5><?php echo __('Check type'); ?></h5>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   id="checkTypeFilterActive"
                                                   class="custom-control-input"
                                                   name="checkbox"
                                                   checked="checked"
                                                   ng-model="filter.Servicestatus.active"
                                                   ng-model-options="{debounce: 500}">
                                            <label class="custom-control-label"
                                                   for="checkTypeFilterActive"><?php echo __('Active service'); ?></label>
                                        </div>

                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   id="checkTypeFilterPassive"
                                                   class="custom-control-input"
                                                   name="checkbox"
                                                   checked="checked"
                                                   ng-model="filter.Servicestatus.passive"
                                                   ng-model-options="{debounce: 500}">
                                            <label class="custom-control-label"
                                                   for="checkTypeFilterPassive"><?php echo __('Passive service'); ?></label>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-xs-12 col-lg-2">
                                    <fieldset>
                                        <h5><?php echo __('Notifications'); ?></h5>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="notificationsFilterEnabled"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.notifications_enabled"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="notificationsFilterEnabled"><?php echo __('Enabled'); ?></label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="notificationsFilterNotEnabled"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Servicestatus.notifications_not_enabled"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="notificationsFilterNotEnabled"><?php echo __('Not enabled'); ?></label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-xs-12 col-lg-2">
                                    <fieldset>
                                        <h5><?php echo __('Priority'); ?></h5>
                                        <div class="form-group smart-form">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="priority1"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Services.priority[1]"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="priority1">
                                                    <i class="fa fa-fire fa-lg ok-soft"></i>
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="priority2"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Services.priority[2]"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="priority2">
                                                    <i class="fa fa-fire fa-lg ok"></i>
                                                    <i class="fa fa-fire fa-lg ok"></i>
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="priority3"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Services.priority[3]"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="priority3">
                                                    <i class="fa fa-fire fa-lg warning"></i>
                                                    <i class="fa fa-fire fa-lg warning"></i>
                                                    <i class="fa fa-fire fa-lg warning"></i>
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="priority4"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Services.priority[4]"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="priority4">
                                                    <i class="fa fa-fire fa-lg critical-soft"></i>
                                                    <i class="fa fa-fire fa-lg critical-soft"></i>
                                                    <i class="fa fa-fire fa-lg critical-soft"></i>
                                                    <i class="fa fa-fire fa-lg critical-soft"></i>
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       id="priority5"
                                                       class="custom-control-input"
                                                       name="checkbox"
                                                       checked="checked"
                                                       ng-model="filter.Services.priority[5]"
                                                       ng-model-options="{debounce: 500}">
                                                <label class="custom-control-label"
                                                       for="priority5">
                                                    <i class="fa fa-fire fa-lg critical"></i>
                                                    <i class="fa fa-fire fa-lg critical"></i>
                                                    <i class="fa fa-fire fa-lg critical"></i>
                                                    <i class="fa fa-fire fa-lg critical"></i>
                                                    <i class="fa fa-fire fa-lg critical"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="float-right">
                                <button type="button" ng-click="resetFilter()"
                                        class="btn btn-xs btn-danger">
                                    <?php echo __('Reset Filter'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- End Filter -->

                    <div class="frame-wrap">
                        <table class="table table-striped m-0 table-bordered table-hover table-sm">
                            <thead>
                            <tr>
                                <th colspan="2" class="no-sort" ng-click="orderBy('Servicestatus.current_state')">
                                    <i class="fa" ng-class="getSortClass('Servicestatus.current_state')"></i>
                                    <?php echo __('State'); ?>
                                </th>

                                <th class="no-sort text-center">
                                    <i class="fa fa-user" title="<?php echo __('is acknowledged'); ?>"></i>
                                </th>

                                <th class="no-sort text-center">
                                    <i class="fa fa-power-off"
                                       title="<?php echo __('is in downtime'); ?>"></i>
                                </th>
                                <th class="no-sort text-center"
                                    ng-click="orderBy('Servicestatus.notifications_enabled')">
                                    <i class="fa" ng-class="getSortClass('Servicestatus.notifications_enabled')"></i>
                                    <i class="fas fa-envelope" title="<?php echo __('Notifications enabled'); ?>">
                                    </i>
                                </th>

                                <th class="no-sort text-center">
                                    <i class="fa fa-lg fa-area-chart" title="<?php echo __('Grapher'); ?>"></i>
                                </th>

                                <th class="no-sort text-center">
                                    <strong title="<?php echo __('Passively transferred service'); ?>">
                                        P
                                    </strong>
                                </th>

                                <th class="no-sort text-center" ng-click="orderBy('servicepriority')">
                                    <i class="fa" ng-class="getSortClass('servicepriority')"></i>
                                    <i class="fa fa-fire" title="<?php echo __('Priority'); ?>">
                                    </i>
                                </th>

                                <th class="no-sort" ng-click="orderBy('servicename')">
                                    <i class="fa" ng-class="getSortClass('servicename')"></i>
                                    <?php echo __('Service name'); ?>
                                </th>


                                <th class="no-sort tableStatewidth"
                                    ng-click="orderBy('Servicestatus.last_state_change')">
                                    <i class="fa" ng-class="getSortClass('Servicestatus.last_state_change')"></i>
                                    <?php echo __('Last state change'); ?>
                                </th>

                                <th class="no-sort tableStatewidth" ng-click="orderBy('Servicestatus.last_check')">
                                    <i class="fa" ng-class="getSortClass('Servicestatus.last_check')"></i>
                                    <?php echo __('Last check'); ?>
                                </th>

                                <th class="no-sort tableStatewidth" ng-click="orderBy('Servicestatus.next_check')">
                                    <i class="fa" ng-class="getSortClass('Servicestatus.next_check')"></i>
                                    <?php echo __('Next check'); ?>
                                </th>

                                <th class="no-sort" ng-click="orderBy('Servicestatus.output')">
                                    <i class="fa" ng-class="getSortClass('Servicestatus.output')"></i>
                                    <?php echo __('Service output'); ?>
                                </th>

                                <th class="no-sort text-center editItemWidth">
                                    <i class="fa fa-gear"></i>
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr ng-repeat-start="service in services"
                                ng-if="services[$index-1].Host.uuid !== service.Host.uuid">
                                <td colspan="13" class="service_table_host_header">

                                    <hoststatusicon host="service"></hoststatusicon>

                                    <?php if ($this->Acl->hasPermission('browser', 'hosts')): ?>
                                        <a class="padding-left-5 txt-color-blueDark"
                                           ui-sref="HostsBrowser({id: service.Host.id})">
                                            {{service.Host.hostname}} ({{service.Host.address}})
                                        </a>
                                    <?php else: ?>
                                        {{service.Host.hostname}} ({{service.Host.address}})
                                    <?php endif; ?>

                                    <?php if ($this->Acl->hasPermission('serviceList', 'services')): ?>
                                        <a class="pull-right txt-color-blueDark"
                                           ui-sref="ServicesServiceList({id: service.Host.id})">
                                            <i class="fa fa-list"
                                               title=" <?php echo __('Go to Service list'); ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                    <div class="pull-right padding-right-30">
                                        <i class="far fa-clock"></i>
                                        <?= __('State since'); ?> {{ service.Hoststatus.last_state_change }}
                                    </div>
                                </td>
                            </tr>

                            <tr ng-repeat-end="">
                                <td class="width-5">
                                    <input type="checkbox"
                                           ng-model="massChange[service.Service.id]"
                                           ng-show="service.Service.allow_edit">
                                </td>

                                <td class="text-center">
                                    <servicestatusicon service="service"></servicestatusicon>
                                </td>

                                <td class="text-center">
                                    <i class="far fa-user"
                                       ng-show="service.Servicestatus.problemHasBeenAcknowledged"
                                       ng-if="service.Servicestatus.acknowledgement_type == 1"></i>

                                    <i class="fas fa-user"
                                       ng-show="service.Servicestatus.problemHasBeenAcknowledged"
                                       ng-if="service.Servicestatus.acknowledgement_type == 2"
                                       title="<?php echo __('Sticky Acknowledgedment'); ?>"></i>
                                </td>

                                <td class="text-center">
                                    <i class="fa fa-power-off"
                                       ng-show="service.Servicestatus.scheduledDowntimeDepth > 0"></i>
                                </td>

                                <td class="text-center">
                                    <div class="icon-stack margin-right-5"
                                         ng-show="service.Servicestatus.notifications_enabled">
                                        <i class="fas fa-envelope opacity-100 "></i>
                                        <i class="fas fa-check opacity-100 fa-xs text-success cornered cornered-lr"></i>
                                    </div>
                                    <div class="icon-stack margin-right-5"
                                         ng-hide="service.Servicestatus.notifications_enabled">
                                        <i class="fas fa-envelope opacity-100 "></i>
                                        <i class="fas fa-times opacity-100 fa-xs text-danger cornered cornered-lr"></i>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <?php if ($this->Acl->hasPermission('browser', 'services')): ?>
                                        <a ui-sref="ServicesBrowser({id:service.Service.id})"
                                           class="txt-color-blueDark"
                                           ng-mouseenter="mouseenter($event, service.Host.uuid, service.Service.uuid)"
                                           ng-mouseleave="mouseleave()"
                                           ng-if="service.Service.has_graph">
                                            <i class="fa fa-lg fa-area-chart">
                                            </i>
                                        </a>
                                    <?php else: ?>
                                        <div ng-mouseenter="mouseenter($event, service.Host.uuid, service.Service.uuid)"
                                             ng-mouseleave="mouseleave()"
                                             ng-if="service.Service.has_graph">
                                            <i class="fa fa-lg fa-area-chart">
                                            </i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <strong title="<?php echo __('Passively transferred service'); ?>"
                                            ng-show="service.Service.active_checks_enabled === false || service.Host.is_satellite_host === true">
                                        P
                                    </strong>
                                </td>

                                <td class="text-center">
                                    <i class="fa fa-fire"
                                       ng-class="{'ok-soft' : service.Service.priority==1,
                                        'ok' : service.Service.priority==2, 'warning' : service.Service.priority==3,
                                        'critical-soft' : service.Service.priority==4, 'critical' : service.Service.priority==5}">
                                    </i>
                                </td>

                                <td>
                                    <?php if ($this->Acl->hasPermission('browser', 'services')): ?>
                                        <a ui-sref="ServicesBrowser({id:service.Service.id})">
                                            {{ service.Service.servicename }}
                                        </a>
                                    <?php else: ?>
                                        {{ service.Service.servicename }}
                                    <?php endif; ?>
                                </td>

                                <td>
                                    {{ service.Servicestatus.last_state_change }}
                                </td>

                                <td>
                                    <span
                                        ng-if="service.Service.active_checks_enabled && service.Host.is_satellite_host === false">{{
                                        service.Servicestatus.lastCheck }}</span>
                                    <span ng-if="service.Service.active_checks_enabled === false">
                                        <?php echo __('n/a'); ?>
                                    </span>
                                </td>

                                <td>
                                    <span
                                        ng-if="service.Service.active_checks_enabled && service.Host.is_satellite_host === false">{{
                                        service.Servicestatus.nextCheck }}</span>
                                    <span
                                        ng-if="service.Service.active_checks_enabled === false || service.Host.is_satellite_host === true">
                                        <?php echo __('n/a'); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="word-break"
                                         ng-bind-html="service.Servicestatus.outputHtml | trustAsHtml"></div>
                                </td>

                                <td class="width-50">
                                    <div class="btn-group btn-group-xs" role="group">
                                        <?php if ($this->Acl->hasPermission('edit', 'services')): ?>
                                            <a ui-sref="ServicesEdit({id: service.Service.id})"
                                               ng-if="service.Service.allow_edit"
                                               class="btn btn-default btn-lower-padding">
                                                <i class="fa fa-cog"></i>
                                            </a>
                                            <a href="javascript:void(0);"
                                               ng-if="!service.Service.allow_edit"
                                               class="btn btn-default btn-lower-padding disabled">
                                                <i class="fa fa-cog"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="javascript:void(0);"
                                               class="btn btn-default btn-lower-padding disabled">
                                                <i class="fa fa-cog"></i></a>
                                        <?php endif; ?>
                                        <button type="button"
                                                class="btn btn-default dropdown-toggle btn-lower-padding"
                                                data-toggle="dropdown">
                                            <i class="caret"></i>
                                        </button>
                                        <!-- <ul class="dropdown-menu" id="menuHack-{{service.Service.uuid}}" > -->
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <?php if ($this->Acl->hasPermission('edit', 'services')): ?>
                                                <a ui-sref="ServicesEdit({id: service.Service.id})"
                                                   ng-if="service.Service.allow_edit"
                                                   class="dropdown-item">
                                                    <i class="fa fa-cog"></i>
                                                    <?php echo __('Edit'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($this->Acl->hasPermission('copy', 'services')): ?>
                                                <a ui-sref="ServicesCopy({ids: service.Service.id})"
                                                   ng-if="service.Service.allow_edit"
                                                   class="dropdown-item">
                                                    <i class="fas fa-files-o"></i>
                                                    <?php echo __('Copy'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($this->Acl->hasPermission('deactivate', 'services')): ?>
                                                <a ng-if="service.Service.allow_edit"
                                                   class="dropdown-item"
                                                   href="javascript:void(0);"
                                                   ng-click="confirmDeactivate(getObjectForDelete(service))">
                                                    <i class="fa fa-plug"></i>
                                                    <?php echo __('Disable'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php
                                            $AdditionalLinks = new \App\Lib\AdditionalLinks($this);
                                            echo $AdditionalLinks->getLinksAsHtmlList('services', 'index', 'list');
                                            ?>
                                            <?php if ($this->Acl->hasPermission('delete', 'services')): ?>
                                                <div class="dropdown-divider"></div>
                                                <a ng-click="confirmDelete(getObjectForDelete(service))"
                                                   ng-if="service.Service.allow_edit"
                                                   class="dropdown-item txt-color-red">
                                                    <i class="fa fa-trash"></i>
                                                    <?php echo __('Delete'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="margin-top-10" ng-show="services.length == 0">
                            <div class="text-center text-danger italic">
                                <?php echo __('No entries match the selection'); ?>
                            </div>
                        </div>
                        <div class="row margin-top-10 margin-bottom-10">
                            <div class="col-xs-12 col-md-2 text-muted text-center">
                                <span ng-show="selectedElements > 0">({{selectedElements}})</span>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <span ng-click="selectAll()" class="pointer">
                                    <i class="fas fa-lg fa-check-square"></i>
                                    <?php echo __('Select all'); ?>
                                </span>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <span ng-click="undoSelection()" class="pointer">
                                    <i class="fas fa-lg fa-square"></i>
                                    <?php echo __('Undo selection'); ?>
                                </span>
                            </div>

                            <?php if ($this->Acl->hasPermission('copy', 'services')): ?>
                                <div class="col-xs-12 col-md-2">
                                    <a ui-sref="ServicesCopy({ids: linkForCopy()})" class="a-clean">
                                        <i class="fas fa-lg fa-files-o"></i>
                                        <?php echo __('Copy'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($this->Acl->hasPermission('delete', 'services')): ?>
                                <div class="col-xs-12 col-md-2 txt-color-red">
                                    <span ng-click="confirmDelete(getObjectsForDelete())" class="pointer">
                                        <i class="fas fa-trash"></i>
                                        <?php echo __('Delete selected'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-default dropdown-toggle waves-effect waves-themed" type="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo __('More actions'); ?>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-start"
                                     style="position: absolute; will-change: top, left; top: 37px; left: 0px;">
                                    <a ng-href="{{ linkForPdf() }}" class="dropdown-item">
                                        <i class="fa fa-file-pdf-o"></i> <?php echo __('List as PDF'); ?>
                                    </a>
                                    <?php if ($this->Acl->hasPermission('deactivate', 'services')): ?>
                                        <a
                                            class="dropdown-item"
                                            href="javascript:void(0);"
                                            ng-click="confirmDeactivate(getObjectsForDelete())">
                                            <i class="fa fa-plug"></i>
                                            <?php echo __('Disable services'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($this->Acl->hasPermission('add', 'servicegroups')): ?>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           ng-click="confirmAddServicesToServicegroup(getObjectsForDelete())">
                                            <i class="fa fa-cogs"></i>
                                            <?php echo __('Add to service group'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($this->Acl->hasPermission('externalcommands', 'hosts')): ?>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           ng-click="reschedule(getObjectsForExternalCommand())">
                                            <i class="fa fa-refresh"></i> <?php echo __('Reset check time'); ?>
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           ng-click="disableNotifications(getObjectsForExternalCommand())">
                                            <i class="fa fa-envelope"></i> <?php echo __('Disable notification'); ?>
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           ng-click="enableNotifications(getObjectsForExternalCommand())">
                                            <i class="fa fa-envelope"></i> <?php echo __('Enable notifications'); ?>
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           ng-click="serviceDowntime(getObjectsForExternalCommand())">
                                            <i class="fa fa-clock-o"></i> <?php echo __('Set planned maintenance times'); ?>
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           ng-click="acknowledgeService(getObjectsForExternalCommand())">
                                            <i class="fa fa-user"></i> <?php echo __('Acknowledge status'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>


                        <scroll scroll="scroll" click-action="changepage" ng-if="scroll"></scroll>
                        <paginator paging="paging" click-action="changepage" ng-if="paging"></paginator>
                        <?php echo $this->element('paginator_or_scroll'); ?>
                    </div>
                </div>
            </div>

            <reschedule-service></reschedule-service>
            <disable-notifications></disable-notifications>
            <enable-notifications></enable-notifications>
            <acknowledge-service author="<?php echo h($username); ?>"></acknowledge-service>
            <service-downtime author="<?php echo h($username); ?>"></service-downtime>

            <popover-graph-directive></popover-graph-directive>

        </div>
    </div>
</div>
