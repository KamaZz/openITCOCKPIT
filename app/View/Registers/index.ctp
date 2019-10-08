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
<div class="subheader">
    <h1 class="subheader-title">
        <i class="subheader-icon fal fa-check-square fa-fw"></i>
        <?php echo __('openITCOCKPIT'); ?>
        <span>>
            <?php echo __('Registration'); ?>
        </span>
    </h1>
</div>

<div class="row">
    <div class="col-xl-12">
        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    Registration <span class="fw-300"><i>Register this openITCOCKPIT instance</i></span>
                </h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse"
                            data-toggle="tooltip" data-offset="0,10" data-original-title="Credits"
                            ng-click="toggleFullscreenMode();"><i class="fal fa-heart"></i></button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <form ng-submit="submit();">
                        <div class="form-group">
                            <div class="form-group">
                                <label class="form-label" for="licenseKey"><?php echo __('License key'); ?></label>
                                <input
                                        class="form-control"
                                        type="text"
                                    <?php if ($disableAutocomplete): ?>
                                        autocomplete="off"
                                    <?php endif; ?>
                                        ng-model="post.Registers.license">
                                <div ng-repeat="error in errors.license">
                                    <div class="invalid-feedback">{{ error }}</div>
                                </div>
                                <div class="help-block">
                                    <a href="https://openitcockpit.io/#Subscription" target="_blank">
                                        <i class="far fa-external-link-square"></i>
                                        <?php echo __('Get your openITCOCKPIT Enterprise Subscription today'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row" ng-show="valid">
                            <div class="col-xs-12 col-md-12 col-lg-12">

                                <div class="well" style="margin-bottom: 20px; padding: 10px;">
                                    <h1>
                                        <i class="fa fa-check-circle ok"></i>
                                        <span class="semi-bold"><?php echo __('Valid openITCOCKPIT Enterprise license'); ?></span>
                                    </h1>

                                    <dl class="dl-horizontal">
                                        <dt><?php echo __('First name'); ?>:</dt>
                                        <dd class="code-font text-info">{{license.firstname}}</dd>

                                        <dt><?php echo __('Last name'); ?>:</dt>
                                        <dd class="code-font text-info">{{license.lastname}}</dd>

                                        <dt><?php echo __('Email'); ?>:</dt>
                                        <dd class="code-font text-info">{{license.email}}</dd>

                                        <dt><?php echo __('Company'); ?>:</dt>
                                        <dd class="code-font text-info">{{license.company}}</dd>

                                        <dt><?php echo __('Expires'); ?>:</dt>
                                        <dd class="code-font text-info">{{license.expire}}</dd>

                                        <dt><?php echo __('License key'); ?>:</dt>
                                        <dd class="code-font text-info">{{license.licence}}</dd>
                                    </dl>

                                </div>

                            </div>
                        </div>

                        <button class="btn btn-primary ml-auto waves-effect waves-themed"
                                type="submit"><?php echo __('Register'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="credits-container" id="credits-container" style="display:none;">
    <div id="credits">
        <h1 class="credits-h1">openITCOCKPIT</h1>
        <br/>

        <h2 class="credits-h2">Developers</h2>
        <dl>
            <dd>Irina Bering</dd>
            <dd>Daniel Ziegler</dd>
            <dd>Maximilian Pappert</dd>
            <dd>Timo Triebensky</dd>
        </dl>


        <h2 class="credits-h2">Special Thanks</h2>
        <p>Stephan Kraus, Oliver Müller, Laziz Karimov, Robert Braun,<br/>
            René Kleffel, Michael Ziegler, Jonas Rottmann,<br/>
            Johannes Drummer, Jens Michelsons, Jeremy Eder and more...</p>

        <h2 class="credits-h2">Special Thanks</h2>
        <p>Thanks to all the developers for the beautiful libraries.</p>

        <h2 class="credits-h2">Powered by</h2>
        <p>
            <img src="/img/logos/php.png">
        </p>

        <p>
            <img src="/img/logos/We-bake-with-CakePHP.png" style="width: 300px;">
        </p>

        <p>
            <img src="/img/logos/jquery.png">
        </p>

        <p>
            <img src="/img/logos/jquery_ui.png">
        </p>

        <p>
            <img src="/img/logos/AngularJS-medium.png">
        </p>

        <p class="padding-top-80">
            <img src="/img/logos/Statusengine_dark.png">
        </p>

        <div class="credits-fineprint">All trademarks are the property of the trademark owners.</div>

        <br/>
        <br/>
        <h2 class="credits-h2">Sponsored by it-novum</h2>
        <p>
            <img src="/img/logos/it-novum.png">
        </p>

        <br/>
        <br/>
        <br/>
        <p id="credits-oitc-logo">
            <img src="/img/logos/openITCOCKPIT_dark.png" style="width: 600px;">
        </p>

        <br/>
        <br/>
        <div class="credits-fineprint">Press ESC to exit</div>

    </div>
</div>