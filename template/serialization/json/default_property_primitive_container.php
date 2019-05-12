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

/** @var bool $isCollection */
/** @var string $propertyName */
/** @var string $propertyConstName */
/** @var string $propertyConstNameExt */
/** @var string $getter */

ob_start();
if ($isCollection) : ?>
        if ([] !== ($vs = $this-><?php echo $getter; ?>())) {
            $a[self::<?php echo $propertyConstName; ?>] = [];
            $a[self::<?php echo $propertyConstNameExt; ?>] = [];
            foreach ($vs as $v) {
                $a[self::<?php echo $propertyConstName; ?>][] = (string)$v;
                $a[self::<?php echo $propertyConstNameExt; ?>][] = $v;
            }
        }
<?php else : ?>
        if (null !== ($v = $this-><?php echo $getter; ?>())) {
            $a[self::<?php echo $propertyConstName; ?>] = (string)$v;
            $a[self::<?php echo $propertyConstNameExt; ?>] = $v;
        }
<?php endif;
return ob_get_clean();