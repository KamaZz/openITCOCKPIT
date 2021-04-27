<?php
// Copyright (C) <2018>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, version 3 of the License.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// 2.
//  If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//  under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//  License agreement and license key will be shipped with the order
//  confirmation.


use itnovum\openITCOCKPIT\ConfigGenerator\Gearman;

/** @var Gearman $Gearman */
?>


<form ng-submit="submit();" class="form-horizontal">
    <?php foreach ($Gearman->getDefaults()['int'] as $key => $defaultValue): ?>
        <div class="form-group required" ng-class="{'has-error': errors.Configfile.<?php echo $key; ?>}">
            <label class="control-label">
                <?php echo h($key); ?>
            </label>
            <input
                class="form-control"
                type="number"
                min="0"
                ng-model="post.int.<?php echo $key; ?>">
            <div ng-repeat="error in errors.Configfile.<?php echo $key; ?>">
                <div class="help-block text-danger">{{ error }}</div>
            </div>
            <div class="help-block">
                <?php echo h($Gearman->getHelpText($key)); ?>
            </div>
        </div>
    <?php endforeach; ?>


    <?php foreach ($Gearman->getDefaults()['string'] as $key => $defaultValue): ?>
        <div class="form-group required" ng-class="{'has-error': errors.Configfile.<?php echo $key; ?>}">
            <label class="control-label">
                <?php echo h($key); ?>
            </label>
            <input
                class="form-control"
                type="text"
                ng-model="post.string.<?php echo $key; ?>">
            <div ng-repeat="error in errors.Configfile.<?php echo $key; ?>">
                <div class="help-block text-danger">{{ error }}</div>
            </div>
            <div class="help-block">
                <?php echo h($Gearman->getHelpText($key)); ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="card margin-top-10">
        <div class="card-body">
            <div class="float-right">
                <button class="btn btn-primary"
                        type="submit"><?php echo __('Save'); ?></button>
                <a back-button href="javascript:void(0);" fallback-state='ConfigurationFilesIndex'
                   class="btn btn-default"><?php echo __('Cancel'); ?></a>
            </div>
        </div>
    </div>
</form>
