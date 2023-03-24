<?php

namespace Modules\Shipping\Http\Services;

use Modules\User\Entities\User;
use Modules\Shipping\Entities\Shipping;
use Modules\Country\Entities\Country;


class ShippingService
{
    protected Shipping $shipping;

    protected User $user;

    /**
     * Save a new shipping
     *
     * @param array $requestData
     * @return array
     */
    public function create(array $requestData): array
    {

        $shipping = $this->createShipping($requestData);
        return [
            'res' => true,
            'msg' => 'Shipping address created successfully',
            'data' => $shipping
        ];
    }

    /**
     * Create a new shipping
     *
     * @param array $shippingData
     * @return Shipping
     */
    public function createShipping(array $shippingData): Shipping
    {
        $shippingData['user_id'] = auth()->user()->id;
        $shipping = new Shipping();
        $shipping->fill($shippingData);
        $shipping->save();

        return $shipping;
    }

    /**
     * Get all shipping address by User
     *
     * @return array
     */

    public function getShipping(): array
    {

        $shipping = Shipping::where('user_id', auth()->user()->id)->get();
        $data = [];
        if (!empty($shipping)) {
            foreach ($shipping as $shipping) {

                $country = Country::where('id', $shipping->country)->first();
                $data[] = array(
                    'id' => $shipping->id,
                    'name' => $shipping->name,
                    'country' => $country->name,
                    'street' => $shipping->street,
                    'suite' => $shipping->suite,
                    'state' => $shipping->state,
                    'town' => $shipping->town,
                    'phoneCode' => $shipping->phoneCode,
                    'phone' => $shipping->phone,
                );
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get shipping address by ID
     *
     * @param $requestData
     * @return array
     */
    public function details($requestData): array
    {

        $shipping = Shipping::where('id', $requestData->id)->first();
        $data = '';
        if (!empty($shipping)) {
            $data = array(
                'id' => $shipping->id,
                'name' => $shipping->name,
                'country' => $shipping->country,
                'street' => $shipping->street,
                'suite' => $shipping->suite,
                'state' => $shipping->state,
                'town' => $shipping->town,
                'phoneCode' => $shipping->phoneCode,
                'phone' => $shipping->phone,
                'zip' => $shipping->zip,
            );
        }


        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Update shipping address BY ID
     *
     * @param array $shippingData
     * @return array
     */

    public function update(array $shippingData): array
    {

        $id = $shippingData['id'];
        $name = $shippingData['name'];
        $country = $shippingData['country'];
        $street = $shippingData['street'];
        $suite = $shippingData['suite'];
        $state = $shippingData['state'];
        $town = $shippingData['town'];
        $zip = $shippingData['zip'];
        $phoneCode = $shippingData['phoneCode'];
        $data = array(
            'name' => $name,
            'country' => $country,
            'street' => $street,
            'suite' => $suite,
            'state' => $state,
            'town' => $town,
            'zip' => $zip,
            'phoneCode' => $phoneCode
        );
        Shipping::where('id', $id)->update($data);


        return [
            'res' => true,
            'msg' => 'Updated Successfully',
            'data' => ''
        ];
    }

    /**
     * Delete Shipping by ID
     *
     * @param $id
     * @return array
     */
    public function delete($id): array
    {
        $shipping = Shipping::where('id', $id)->first();
        if (empty($shipping)) {

            return [
                'res' => false,
                'msg' => 'Shipping not found !',
                'data' => ""
            ];
        }
        $shipping->delete();

        return [
            'res' => true,
            'msg' => 'Shipping Address successfully deleted',
            'data' => ""
        ];
    }

}
