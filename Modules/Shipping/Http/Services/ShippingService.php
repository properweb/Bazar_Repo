<?php

namespace Modules\Shipping\Http\Services;

use Modules\Program\Entities\Program;
use Modules\User\Entities\User;
use Modules\Shipping\Entities\Shipping;
use DB;


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
        $response = [
            'res' => true,
            'msg' => 'Shipping address created successfully',
            'data' => $shipping
        ];

        return $response;
    }

    /**
     * Create a new shipping
     *
     * @param array $shippingData
     * @return Shipping
     */
    public function createShipping(array $shippingData): Shipping
    {

        $shipping = new Shipping();
        $shipping->fill($shippingData);
        $shipping->save();

        return $shipping;
    }

    /**
     * @param $requestData
     * @return array
     */

    public function getShippings($requestData): array
    {


        $shippings = Shipping::where('user_id', $requestData->user_id)->get();
        $data = [];
        if (!empty($shippings)) {
            foreach ($shippings as $shipping) {
                $country = DB::table('countries')->where('id', $shipping->country)->first();
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
     * @param $requestData
     * @return array
     */
    public function details($requestData): array
    {


        $shippings = Shipping::where('id', $requestData->id)->first();

        if (!empty($shippings)) {


                $data = array(
                    'id' => $shippings->id,
                    'name' => $shippings->name,
                    'country' => $shippings->country,
                    'street' => $shippings->street,
                    'suite' => $shippings->suite,
                    'state' => $shippings->state,
                    'town' => $shippings->town,
                    'phoneCode' => $shippings->phoneCode,
                    'phone' => $shippings->phone,
                    'zip' => $shippings->zip,
                );
            }


        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * @param array $shippingData
     * @return array
     */

    public function update(array $shippingData): array
    {
        //update Program
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

        $response = [
            'res' => true,
            'msg' => 'Updated Successfully',
            'data' => ''
        ];

        return $response;
    }

    /**
     * @param $requestData
     * @return array
     */
    public function delete($id,$user_id): array
    {
        $shipping = Shipping::where('id', $id)->where('user_id', $user_id)->first();
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
