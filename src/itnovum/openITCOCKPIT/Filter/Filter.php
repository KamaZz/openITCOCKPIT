<?php
// Copyright (C) <2015>  <it-novum GmbH>
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

namespace itnovum\openITCOCKPIT\Filter;

use Cake\Http\ServerRequest;

abstract class Filter {

    /**
     * @var ServerRequest
     */
    protected $Request;

    public function __construct(ServerRequest $Request) {
        $this->Request = $Request;
    }

    /**
     * @param $filters
     * @return array
     * @throws \Cake\Http\Exception\NotImplementedException
     */
    public function getConditionsByFilters($filters) {
        $conditions = [];
        foreach ($filters as $operator => $fields) {
            foreach ($fields as $field) {
                if ($this->queryHasField($field)) {
                    switch ($operator) {
                        case 'bool':
                            $value = $this->getQueryFieldValue($field);
                            if ($value === '1' || $value === 1 || $value === 'true' || $value === true) {
                                $conditions[$field] = 1;
                            }

                            if ($value === '0' || $value === 0 || $value === 'false' || $value === false) {
                                $conditions[$field] = 0;
                            }
                            break;
                        case 'like':
                            $value = $this->getQueryFieldValue($field);
                            if ($value) {
                                $value = str_replace('\\', '\\\\', $value);

                                $conditions[sprintf('%s LIKE', $field)] = sprintf(
                                    '%%%s%%',
                                    $value
                                );
                            }
                            break;

                        case 'rlike':
                            $value = $this->getQueryFieldValue($field, true);
                            if ($value) {
                                if (!is_array($value)) {
                                    $value = [$value];
                                }
                                $conditions[sprintf('%s rlike', $field)] = sprintf('.*(%s).*', implode('|', $value));
                            }
                            break;
                        case 'notrlike':
                            $value = $this->getQueryFieldValue($field, true);
                            if ($value) {
                                if (!is_array($value)) {
                                    $value = [$value];
                                }
                                $conditions[sprintf('%s not rlike', $field)] = sprintf('.*(%s).*', implode('|', $value));
                            }
                            break;
                        case 'equals':
                            $values = $this->getQueryFieldValue($field);
                            if (is_array($values) && !empty($values)) {
                                $conditions[sprintf('%s IN', $field)] = $values;
                            } else {
                                if ($values || $values === '0') {
                                    $conditions[$field] = $values;
                                }
                            }
                            break;

                        case 'greater':
                            $values = $this->getQueryFieldValue($field);
                            if ($values || $values === '0') {
                                $conditions[sprintf('%s >', $field)] = $values;
                            }
                            break;

                        case 'greater_equals':
                            $values = $this->getQueryFieldValue($field);
                            if ($values || $values === '0') {
                                $conditions[sprintf('%s >=', $field)] = $values;
                            }
                            break;

                        case 'lesser':
                            $values = $this->getQueryFieldValue($field);
                            if ($values || $values === '0') {
                                $conditions[sprintf('%s <', $field)] = $values;
                            }
                            break;

                        case 'lesser_equals':
                            $values = $this->getQueryFieldValue($field);
                            if ($values || $values === '0') {
                                $conditions[sprintf('%s <=', $field)] = $values;
                            }
                            break;

                        case 'state':
                            $values = $this->mapStateNameToStateId($field);
                            if (is_array($values) && !empty($values)) {
                                $conditions[sprintf('%s IN', $field)] = $values;
                            } else {
                                if ($values) {
                                    $conditions[$field] = $values;
                                }
                            }

                            break;

                        case 'downtime':
                            $value = $this->getQueryFieldValue($field);
                            if ($value === '1' || $value === 1 || $value === 'true' || $value === true) {
                                $conditions[sprintf('%s >=', $field)] = 1;
                            }
                            if ($value === '0' || $value === 0 || $value === 'false' || $value === false) {
                                $conditions[sprintf('%s =', $field)] = 0;
                            }
                            break;

                        default:
                            throw new \Cake\Http\Exception\NotImplementedException('This filter type is not implemented yet');
                    }
                }
            }
        }
        return $conditions;
    }

    /**
     * @param $field
     * @return bool
     */
    public function queryHasField($field) {
        if ($this->Request->is('post')) {
            // POST Request
            $query = $this->Request->getData('filter');
        } else {
            // GET Request
            $query = $this->Request->getQuery('filter');
        }
        return isset($query[$field]);
    }

    /**
     * @param $field
     * @return null|mixed
     */
    public function getQueryFieldValue($field, $strict = false) {
        if ($this->queryHasField($field)) {
            if ($strict === false) {
                if ($this->Request->is('post')) {
                    // POST Request
                    $query = $this->Request->getData('filter');
                } else {
                    // GET Request
                    $query = $this->Request->getQuery('filter');
                }
                return $query[$field];
            }

            if ($strict === true) {
                if ($this->Request->is('post')) {
                    // POST Request
                    $query = $this->Request->getData('filter');
                } else {
                    // GET Request
                    $query = $this->Request->getQuery('filter');
                }
                $value = $query[$field];
                if (is_array($value)) {
                    $value = array_filter($value, function ($val) {
                        if ($val === null || $val === '') {
                            return false;
                        }
                        return true;
                    });
                }
                if (!empty($value)) {
                    return $value;
                }
            }

        }
        return null;
    }

    public function mapStateNameToStateId($field) {
        $values = $this->getQueryFieldValue($field);
        if (!is_array($values)) {
            $values = [$values];
        }
        $return = [];
        foreach ($values as $value) {
            switch ($value) {
                case 'up':
                case 'ok':
                    $return[] = 0;
                    break;
                case 'down':
                case 'warning':
                    $return[] = 1;
                    break;
                case 'unreachable':
                case 'critical':
                    $return[] = 2;
                    break;
                case 'unknown':
                    $return[] = 3;
                    break;
            }
        }

        return $return;
    }

    /**
     * This parameter needs to be passed via the query string (GET)
     *
     * @param string $default
     * @return string|array
     */
    public function getSort($default = '') {
        $sort = $this->Request->getQuery('sort');

        if ($sort !== null && $sort !== '') {
            return $sort;
        }
        return $default;
    }

    /**
     * This parameter needs to be passed via the query string (GET)
     *
     * @param string $default
     * @return string
     */
    public function getDirection($default = '') {
        if ($this->Request->getQuery('direction', null) === 'desc') {
            return 'desc';
        }

        if ($this->Request->getQuery('direction', null) === 'asc') {
            return 'asc';
        }

        if ($default === '') {
            return 'asc';
        }

        return $default;
    }

    /**
     * @param array $sortAsArray
     * @return array
     */
    public function validateArrayDirection($sortAsArray = []) {
        $validatedSort = [];
        foreach ($sortAsArray as $sortField => $sortDirection) {
            $validatedSort[$sortField] = ($sortDirection === 'desc') ? 'desc' : 'asc';
        }
        return $validatedSort;
    }

    /**
     * @param string $defaultSort
     * @param string $defaultDirection
     * @return array
     */
    public function getOrderForPaginator($defaultSort = '', $defaultDirection = '') {
        if (is_array($this->getSort($defaultSort))) {
            return $this->validateArrayDirection($this->getSort($defaultSort));
        }

        return [
            $this->getSort($defaultSort) => $this->getDirection($defaultDirection)
        ];
    }

    /**
     * This parameter needs to be passed via the query string (GET)
     *
     * @param int $default
     * @return int
     */
    public function getPage($default = 1) {

        if ($this->Request->getQuery('page', 0) > 0) {
            return (int)$this->Request->getQuery('page');
        }
        return $default;
    }

}
