<?php

namespace Modules\Product\Rules;


use Illuminate\Contracts\Validation\Rule;

class CheckOptionDuplicates implements Rule
{
    /**
     * Run the validation rule.
     */
    public function passes($attribute, $variations)
    {

        foreach ($variations as $vars) {

            if (!empty($vars['value1'])) {
                $value1 = $vars['value1'];
            } else {
                $value1 = '';
            }
            if (!empty($vars['value2'])) {
                $value2 = $vars['value2'];
            } else {
                $value2 = '';
            }
            if (!empty($vars['value3'])) {
                $value3 = $vars['value3'];
            } else {
                $value3 = '';
            }

            $data[] = array(
                $value1 . '-' . $value2 . '-' . $value3

            );

        }
        if (empty($data)) {
            return true;
        }

        for ($i = 0; $i < count($data); $i++) {
            for ($j = $i + 1; $j < count($data); $j++) {
                if ($data[$i] == $data[$j]) {
                    return false;
                }
            }
        }
        return true;

    }

    /**
     * Error message
     *
     * @return string
     */
    public function message(): string
    {
        return 'Duplicate variations are not allowed !';
    }
}
