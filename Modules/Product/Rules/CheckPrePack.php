<?php

namespace Modules\Product\Rules;


use Illuminate\Contracts\Validation\Rule;

class CheckPrePack implements Rule
{
    /**
     * Run the validation rule.
     */
    public function passes($attribute, $repack)
    {
      if(empty($repack))
      {
          return true;
      }

        foreach ($repack as $vars) {

            if (!empty($vars['status']) && $vars['status'] == 'published') {
               if(empty($vars['pack_name']))
               {
                   return false;
               }
                if(empty($vars['size_ratio']))
                {
                    return false;
                }
                if(empty($vars['size_range_value']))
                {
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
        return 'Please enter all the details of prepack !';
    }
}
