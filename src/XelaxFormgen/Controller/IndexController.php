<?php

/*
 * Copyright (C) 2015 schurix
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace XelaxFormgen\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Description of IndexController
 *
 * @author schurix
 */
class IndexController extends AbstractActionController{
	
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	
	/**
	 * @var \XelaxFormgen\Options\FormgenOptions
	 */
	protected $formgenOptions;
	
	/**
	 * @var PhpRenderer
	 */
	protected $renderer;
	
	/**
	 * @param EntityManager $em
	 */
	public function setEntityManager(EntityManager $em){
		$this->em = $em;
	}
	
	/**
	 * @return EntityManager
	 */
	public function getEntityManager(){
		if (null === $this->em) {
			$this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		}
		return $this->em;
	}
	
	public function getFormgenOptions(){
		if(null === $this->formgenOptions){
			$this->formgenOptions = $this->getServiceLocator()->get('XelaxFormgen\Options\Formgen');
		}
		return $this->formgenOptions;
	}
	
	public function indexAction(){
		$request = $this->getRequest();
		
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}
		
		$module = $request->getParam('module');
		
		$entity = $request->getParam('entity');
		
		if(!empty($module) && !empty($entity)){
			throw new \RuntimeException('You can not use module and entity parameter together');
		}
		
		$em = $this->getEntityManager();
		$options = $this->getFormgenOptions();
		$renderer = $this->getRenderer();
		
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		foreach($metadata as $data){
			/* @var $data \Doctrine\ORM\Mapping\ClassMetadata */
			
			if(empty($module) && empty($entity) && !$this->pathStartsWith($data->getReflectionClass()->getFileName(), $options->getModulesPath())){
				continue;
			}
			
			if(!empty($module) && !$this->pathStartsWith($data->getName(), $module)){
				continue;
			}
			
			if(!empty($entity) && $data->getName() !== $entity){
				continue;
			}
			
			$fields = array();
			$fieldNames = $data->getFieldNames();
			foreach($fieldNames as $fieldName){
				$mapping = $data->getFieldMapping($fieldName);
				
				// skip fields declared in superclasses
				if(isset($mapping['declared']) && $mapping['declared'] !== $data->getName()){
					continue;
				}
				
				$field = array(
					'name' => $mapping['fieldName'],
					'type' => $mapping['type'],
					'id' => isset($mapping['id']) ? $mapping['id'] : false,
				);
				$fields[] = $field;
			}
			
			$associationNames = $data->getAssociationNames();
			foreach($associationNames as $assocciationName){
				$association = $data->getAssociationMapping($assocciationName);
				if($association['type'] & ClassMetadataInfo::TO_ONE){
					$field = array(
						'name' => $association['fieldName'],
						'type' => 'object',
						'targetEntity' => $association['targetEntity'],
						'id' => isset($mapping['id']) ? $mapping['id'] : false,
					);
				}
			}
			
			$entityClass = $data->getName();
			$entityName = substr($data->getName(), strrpos($data->getName(), '\\')+1);
			$baseNamespace = substr($entityClass, 0, strpos($entityClass, '\\'));
			$formNamespace = sprintf($options->getFormNamespace(), $baseNamespace);
			$fieldsetNamespace = sprintf($options->getFieldsetNamespace(), $baseNamespace);
			$fieldsetClass = sprintf('%s\\%sFieldset', $fieldsetNamespace, $entityName);
			$formClass = sprintf('%s\\%sForm', $formNamespace, $entityName);
			
			// check if there is a superclass to inherit from
			$parentEntity = $data->getReflectionClass()->getParentClass();
			if ($parentEntity){
				if(!$em->getMetadataFactory()->getMetadataFor($parentEntity->getName())){
					$parentEntity = false;
				}
			}
			$parentFieldset = null;
			if($parentEntity){
				$parentEntityClass = $parentEntity->getName();
				$parentEntityName = substr($parentEntityClass, strrpos($parentEntityClass, '\\')+1);
				$parentBaseNamespace = substr($parentEntityClass, 0, strpos($parentEntityClass, '\\'));
				$parentFieldsetNamespace = sprintf($options->getFieldsetNamespace(), $parentBaseNamespace);
				$parentFieldset = sprintf('%s\\%sFieldset', $parentFieldsetNamespace, $parentEntityName);
			}
			
			
			$params = array(
				'entityClass' => $entityClass,
				'entityName' => $entityName,
				'fields' => $fields,
				'formNamespace' => $formNamespace,
				'fieldsetNamespace' => $fieldsetNamespace,
				'fieldsetClass' => $fieldsetClass,
				'parentEntity' => $parentEntity,
				'parentFieldset' => $parentFieldset
			);
			
			// all generated forms go there
			$datadir = getcwd().'/data/formgen/';
			
			// create fieldset
			$fieldsetFile = str_replace('\\', DIRECTORY_SEPARATOR, $fieldsetClass);
			if(!is_dir(dirname($datadir.$fieldsetFile))){
				mkdir(dirname($datadir.$fieldsetFile), 0777, true);
			}
			$fieldset = $renderer->render('fieldset', $params);
			file_put_contents($datadir.$fieldsetFile.'.php', $fieldset);
			
			// Do not create form for mapped superclass
			if($data->isMappedSuperclass){ 
				continue;
			}
			
			// create form
			$formFile = str_replace('\\', DIRECTORY_SEPARATOR, $formClass);
			if(!is_dir(dirname($datadir.$formFile))){
				mkdir(dirname($datadir.$formFile), 0777, true);
			}
			$form = $renderer->render('form', $params);
			file_put_contents($datadir.$formFile.'.php', $form);
			//echo $renderer->render('fieldset', $params);
		}
		return '';
	}
	
	protected function getRenderer(){
		if(null === $this->renderer){
			$renderer = new PhpRenderer();
			$stack = new TemplatePathStack(array(
				'script_paths' => array(
					__DIR__ . '/../../../templates',
				)
			));
			$renderer->setResolver($stack);
			$this->renderer = $renderer;
		}
		return $this->renderer;
	}
	
	protected function pathStartsWith($path, $start){
		return $start === "" || strpos($path, $start) === 0;
	}
}