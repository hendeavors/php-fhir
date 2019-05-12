<?php

/*
 * Copyright 2016-2019 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use DCarbone\PHPFHIR\Enum\TypeKindEnum;
use DCarbone\PHPFHIR\Utilities\CopyrightUtils;

/** @var \DCarbone\PHPFHIR\Config\VersionConfig $config */
/** @var \DCarbone\PHPFHIR\Definition\Types $types */

// find either Resource Container or Inline.Resource

$containerType = null;
foreach ($types->getIterator() as $type) {
    $kind = $type->getKind();
    if ($kind->isResourceContainer() || $kind->isInlineResource()) {
        $containerType = $type;
        break;
    }
}

if (null === $containerType) {
    throw new \RuntimeException(sprintf(
            'Unable to locate either "%s" or "%s" type',
        TypeKindEnum::RESOURCE_CONTAINER,
        TypeKindEnum::RESOURCE_INLINE
    ));
}

$namespace = $config->getNamespace(false);

$innerTypes = [];
foreach ($containerType->getProperties()->getSortedIterator() as $property) {
    if ($ptype = $property->getValueFHIRType()) {
        $innerTypes[] = $ptype;
    }
}

$imports = [];
foreach ($innerTypes as $innerType) {
    if ($innerType->getFullyQualifiedNamespace(false) !== $namespace) {
        $imports[] = $innerType->getFullyQualifiedClassName(false);
    }
}

$imports = array_unique($imports);
natcasesort($imports);

ob_start();

echo "<?php\n\n";

if ('' !== $namespace) :
    echo "namespace {$namespace};\n\n";
endif;

echo CopyrightUtils::getFullPHPFHIRCopyrightComment();

echo "\n\n";

foreach ($imports as $import) :
    echo "use {$import};\n";
endforeach;

echo "\n";
?>
/**
 * Class PHPFHIRHelpers<?php if ('' !== $namespace) : ?>

 * @package \<?php echo $namespace; ?>
<?php endif;?>

 */
abstract class PHPFHIRHelpers
{
    /**
     * This is th array of resource types that are allowed to be contained within a <?php echo $containerType->getFHIRName(); ?>
     * @var array
     */
    private static $_resourceMap = [<?php foreach($innerTypes as $innerType) :
    ?>
<?php endforeach; ?>    ];


}
<?php return ob_get_clean();