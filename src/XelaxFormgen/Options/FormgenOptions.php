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

namespace XelaxFormgen\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Options for form generator
 *
 * @author schurix
 */
class FormgenOptions extends AbstractOptions{
	protected $modulesPath;
	protected $fieldsetNamespace = '%s\\Form';
	protected $formNamespace = '%s\\Form';
	
	public function __construct($options = null) {
		parent::__construct($options);
		if(!$this->modulesPath){
			$this->modulesPath = getcwd().'/module';
		}
	}
	
	public function getModulesPath() {
		return $this->modulesPath;
	}

	public function setModulesPath($modulesPath) {
		$this->modulesPath = $modulesPath;
		return $this;
	}
	
	public function getFieldsetNamespace() {
		return $this->fieldsetNamespace;
	}

	public function setFieldsetNamespace($fieldsetNamespace) {
		$this->fieldsetNamespace = $fieldsetNamespace;
		return $this;
	}
	
	public function getFormNamespace() {
		return $this->formNamespace;
	}

	public function setFormNamespace($formNamespace) {
		$this->formNamespace = $formNamespace;
		return $this;
	}
}
