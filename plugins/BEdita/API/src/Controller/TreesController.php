<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2020 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\API\Controller;

use BEdita\Core\Model\Action\GetObjectAction;
use BEdita\Core\Model\Entity\ObjectType;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * Controller for `/trees` endpoint.
 *
 * @since 4.2.0
 */
class TreesController extends AppController
{
    use InstanceConfigTrait;

    /**
     * Objects Table.
     *
     * @var \BEdita\Core\Model\Table\ObjectsTable
     */
    protected $Objects;

    /**
     * Trees Table.
     *
     * @var \BEdita\Core\Model\Table\TreesTable
     */
    protected $Trees;

    /**
     * Request object Table.
     *
     * @var \BEdita\Core\Model\Table\ObjectsBaseTable
     */
    protected $Table;

    /**
     * Path ID list
     *
     * @var array
     */
    protected $idList;

    /**
     * Path uname list
     *
     * @var array
     */
    protected $unameList;

    /**
     * Available configurations are:
     *  - `allowedAssociations`: array of relationships of the loaded object
     *
     * @var array
     */
    protected $_defaultConfig = [
        'allowedAssociations' => [],
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->Objects = TableRegistry::getTableLocator()->get('Objects');
        $this->Trees = TableRegistry::getTableLocator()->get('Trees');
    }

    /**
     * Display object on a given path
     *
     * @param string $path Trees path
     * @return \Cake\Http\Response|null
     */
    public function index(string $path)
    {
        $this->request->allowMethod(['get']);

        // populate idList, unameList
        $this->pathDetails($path);

        $parents = $this->parents();

        $ids = array_values($this->idList);
        $entity = $this->loadObject(end($ids));

        $this->checkPath($entity, $parents);

        $entity->set('uname_path', sprintf('/%s', implode('/', $this->unameList)));
        $entity->setAccess('uname_path', false);

        $this->set('_fields', $this->request->getQuery('fields', []));
        $this->set(compact('entity'));
        $this->set('_serialize', ['entity']);

        return null;
    }

    /**
     * Check path validity.
     *
     * @param EntityInterface $entity Object entity.
     * @param array $parents Parents ID array.
     * @return void
     */
    protected function checkPath(EntityInterface $entity, array $parents): void
    {
        if (empty($parents) && $entity->get('type') !== 'folders') {
            throw new NotFoundException(__d('bedita', 'Invalid path'));
        }

        if ($entity->get('type') === 'folders') {
            $idPath = sprintf('/%s', implode('/', $this->idList));
            if ($entity->get('path') !== $idPath) {
                throw new NotFoundException(__d('bedita', 'Invalid path'));
            }

            return;
        }

        $pathFound = array_values($parents);
        $pathFound[] = (int)$entity->get('id');
        if ($this->idList !== $pathFound) {
            throw new NotFoundException(__d('bedita', 'Invalid path'));
        }
    }

    /**
     * Calculate path details:
     *  - idList: ID based path list
     *  - unameList: uname based path list
     *
     * @param string $path Requesed object path
     * @return void
     */
    protected function pathDetails(string $path): void
    {
        $this->unameList = $this->idList = [];
        $pathList = explode('/', $path);
        foreach ($pathList as $p) {
            if (is_numeric($p)) {
                $this->idList[] = (int)$p;
                $this->unameList[] = $this->objectUname((int)$p);
            } else {
                $this->idList[] = $this->Objects->getId($p);
                $this->unameList[] = (string)$p;
            }
        }
    }

    /**
     * Get object uname
     *
     * @param int $id Object ID
     * @return string
     */
    protected function objectUname(int $id): string
    {
        return (string)$this->Objects->find('list', ['valueField' => 'uname'])
            ->where(compact('id'))
            ->firstOrFail();
    }

    /**
     * Get parents object ID array and check object parent existence
     *
     * @return array
     */
    protected function parents(): array
    {
        $count = count($this->idList);
        if ($count === 1) {
            return [];
        }

        $id = $this->idList[$count - 1];
        $parentId = $this->idList[$count - 2];
        $parentCondition = ['object_id' => $id, 'parent_id' => $parentId];
        if (!$this->Trees->exists($parentCondition)) {
            throw new NotFoundException(__d('bedita', 'Invalid path'));
        }

        return $this->Trees->find('pathNodes', [$parentId])
            ->find('list', [
                'keyField' => 'id',
                'valueField' => 'object_id',
            ])
            ->toArray();
    }

    /**
     * Load object entity
     *
     * @param int $id Object ID
     * @return EntityInterface
     */
    protected function loadObject(int $id): EntityInterface
    {
        $res = $this->Objects->find()->where(compact('id'))
            ->select([$this->Objects->aliasField('object_type_id')])
            ->enableHydration(false)
            ->firstOrFail();

        /** @var \BEdita\Core\Model\Entity\ObjectType $objectType */
        $objectType = TableRegistry::getTableLocator()->get('ObjectTypes')->get($res['object_type_id']);
        $this->Table = TableRegistry::getTableLocator()->get($objectType->get('alias'));

        $action = new GetObjectAction(['table' => $this->Table, 'objectType' => $objectType]);

        return $action([
            'primaryKey' => $id,
            'contain' => $this->getContain($objectType),
            'lang' => $this->request->getQuery('lang'),
        ]);
    }

    /**
     * Retrieve `contain` associations array
     *
     * @param ObjectType $objectType Object type entity
     * @return array
     */
    protected function getContain(ObjectType $objectType): array
    {
        $include = $this->request->getQuery('include');
        if (empty($include)) {
            return [];
        }

        $relations = array_keys($objectType->getRelations());
        $this->setConfig('allowedAssociations', $relations);

        return $this->prepareInclude($include);
    }

    /**
     * Find the association corresponding to the relationship name.
     *
     * @param string $relationship Relationship name.
     * @return \Cake\ORM\Association
     * @throws \Cake\Http\Exception\NotFoundException Throws an exception if no association could be found.
     */
    protected function findAssociation($relationship)
    {
        if (in_array($relationship, $this->getConfig('allowedAssociations'))) {
            $association = $this->Table->associations()->getByProperty($relationship);
            if ($association !== null) {
                return $association;
            }
        }

        throw new NotFoundException(__d('bedita', 'Relationship "{0}" does not exist', $relationship));
    }
}
