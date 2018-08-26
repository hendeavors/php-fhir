<?php namespace DCarbone\PHPFHIR;

/*
 * Copyright 2016-2018 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use DCarbone\PHPFHIR\ClassGenerator\Generator\ClassGenerator;
use DCarbone\PHPFHIR\ClassGenerator\Generator\MethodGenerator;
use DCarbone\PHPFHIR\ClassGenerator\Template\PHPFHIR\AutoloaderTemplate;
use DCarbone\PHPFHIR\ClassGenerator\Template\PHPFHIR\ParserMapTemplate;
use DCarbone\PHPFHIR\ClassGenerator\Template\PHPFHIR\ResponseParserTemplate;
use DCarbone\PHPFHIR\Definition\TypeExtractor;
use DCarbone\PHPFHIR\Utilities\CopyrightUtils;
use DCarbone\PHPFHIR\Utilities\FileUtils;

/**
 * Class Generator
 * @package PHPFHIR
 */
class Generator
{
    /** @var \DCarbone\PHPFHIR\Config */
    protected $config;

    /** @var \DCarbone\PHPFHIR\Definition */
    protected $definition;

    /** @var AutoloaderTemplate */
    private $autoloadMap;
    /** @var ParserMapTemplate */
    private $mapTemplate;

    /**
     * Generator constructor.
     * @param \DCarbone\PHPFHIR\Config $config
     */
    public function __construct(Config $config, Definition $definition)
    {
        $this->config = $config;
        $this->definition = $definition;
    }

    /**
     * Generate FHIR object classes based on XSD
     */
    public function generate()
    {
        $this->beforeGeneration();

        $this->config->getLogger()->startBreak('Class Generation');
        foreach ($this->types as $fhirElementName => $mapEntry) {
            $this->config->getLogger()->debug("Generating class for element {$fhirElementName}...");
            $classTemplate = ClassGenerator::buildFHIRElementClassTemplate($this->config, $this->types, $mapEntry);

            FileUtils::createDirsFromNS($classTemplate->getNamespace(), $this->config);

            // Generate class file
            MethodGenerator::implementConstructor($this->config, $classTemplate);
            $classTemplate->writeToFile($this->config->getOutputPath());

            $this->mapTemplate->addEntry($classTemplate);
            $this->autoloadMap->addPHPFHIRClassEntry($classTemplate);
            $this->config->getLogger()->debug("{$fhirElementName} completed.");
        }
        $this->config->getLogger()->endBreak('Class Generation');

        $this->afterGeneration();
    }

    /**
     * Commands to run prior to class generation
     */
    protected function beforeGeneration()
    {
        // Initialize some classes and things.
        $this->config->getLogger()->startBreak('Generator Class Initialization');
        self::_initializeClasses($this->config);

//        $this->autoloadMap = new AutoloaderTemplate($this->config);
//
//        $this->mapTemplate = new ParserMapTemplate($this->config);
//        $this->autoloadMap->addEntry(
//            $this->mapTemplate->getClassName(),
//            $this->mapTemplate->getClassPath()
//        );
//
//        $helperTemplate = new HelperTemplate($this->config);
//        $helperTemplate->writeToFile();
//        $this->autoloadMap->addEntry(
//            $helperTemplate->getClassName(),
//            $helperTemplate->getClassPath()
//        );

        $this->config->getLogger()->endBreak('Generator Class Initialization');
    }

    /**
     * @param \DCarbone\PHPFHIR\Config $config
     */
    private static function _initializeClasses(Config $config)
    {
        $config->getLogger()->info('Compiling Copyrights...');
        CopyrightUtils::compileCopyrights($config);

        $config->getLogger()->info('Creating root directory...');
        FileUtils::createDirsFromNS($config->getOutputNamespace(), $config);
    }

    /**
     * Commands to run after class generation
     */
    protected function afterGeneration()
    {
        $this->mapTemplate->writeToFile();
        $this->autoloadMap->writeToFile();

        $responseParserTemplate = new ResponseParserTemplate($this->config);
        $this->autoloadMap->addEntry(
            $responseParserTemplate->getClassName(),
            $responseParserTemplate->getClassPath()
        );
        $responseParserTemplate->writeToFile();
    }
}