<?php
/*
 * Cintient, Continuous Integration made simple.
 * 
 * Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * . Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * 
 * . Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following
 *   disclaimer in the documentation and/or other materials provided
 *   with the distribution.
 *   
 * . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
 *   the names of its contributors may be used to endorse or promote
 *   products derived from this software without specific prior
 *   written permission.
 *   
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 */

/**
 * 
 */
class UrlManager
{
  static public function getForAsset($filename, $params = array())
  {
    $params['f'] = $filename;
    return CINTIENT_BASE_URL . "/asset/?" .  http_build_query($params);
  }
  
  static public function getForAuthentication()
  {
    return CINTIENT_BASE_URL . '/authentication/';
  }
  
  static public function getForDashboard()
  {
    return CINTIENT_BASE_URL . '/dashboard/';
  }
  
  static public function getForProjectBuild()
  {
    return CINTIENT_BASE_URL . '/project/?build';   
  }
  
  static public function getForProjectBuildView(Project $project, ProjectBuild $build)
  {
    return CINTIENT_BASE_URL . "/project/?pid={$project->getId()}&bid={$build->getId()}";
  }
  
  static public function getForProjectEdit()
  {
    return CINTIENT_BASE_URL . '/project/?pid=' . $_SESSION['project']->getId();
  }
  
  static public function getForProjectNew()
  {
    return CINTIENT_BASE_URL . '/project/?new';
  }
  
  static public function getForProjectView(Project $project)
  {
    return CINTIENT_BASE_URL . "/project/?pid={$project->getId()}";
  }
}