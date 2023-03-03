@include('backend::header')
<div class="container-fluid px-2 px-md-4">

    <?php if(!empty($brand[0]['cover_image'])){?>
      <div class="page-header min-height-300 border-radius-xl mt-4" style="background-image: url('{{asset('public') . '/'.$brand[0]['cover_image']}}');">
        <span class="mask opacity-6"></span>
      </div>
    <?php } ?>
      <div class="card card-body mx-3 mx-md-4 mt-n6">
        <div class="row gx-4 mb-2">
            <?php if(!empty($brand[0]['profile_photo'])){?>
          <div class="col-auto">
            <div class="avatar avatar-xl position-relative">
              <img src="{{asset('public') . '/'.$brand[0]['profile_photo']}}" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
            </div>
          </div>
            <?php } ?>
          <div class="col-auto my-auto">
            <div class="h-100">
              <h5 class="mb-1">
                  {{$brand[0]['brand_name']}}
              </h5>
              <p class="mb-0 font-weight-normal text-sm">
                  {{$brand[0]['name']}}
              </p>
            </div>
          </div>

        </div>
        <div class="row">
          <div class="row">

            <div class="col-12 col-xl-4">
              <div class="card card-plain h-100">
                <div class="card-header pb-0 p-3">
                  <div class="row">
                    <div class="col-md-8 d-flex align-items-center">
                      <h6 class="mb-0">Personal Information</h6>
                    </div>

                  </div>
                </div>
                <div class="card-body p-3">


                  <ul class="list-group">
                    <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Full Name:</strong> {{$brand[0]['name']}}</li>
                    <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Mobile:</strong> &nbsp; {{$brand[0]['phone_number']}}</li>
                    <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Email:</strong> &nbsp; {{$brand[0]['email']}}</li>
                    <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Gender:</strong> &nbsp; {{$brand[0]['gender']}}</li>
                      <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Gender:</strong> &nbsp; {{$brand[0]['country']}}</li>

                  </ul>
                </div>
              </div>
            </div>
            <div class="col-12 col-xl-4">
              <div class="card card-plain h-100">
                <div class="card-header pb-0 p-3">
                  <h6 class="mb-0">Brand Information</h6>
                </div>
                  <div class="card-body p-3">
          <ul class="list-group">
              <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Name:</strong> {{$brand[0]['brand_name']}}</li>
              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Website:</strong> &nbsp; {{$brand[0]['website_url']}}</li>
              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Instagram Handle:</strong> &nbsp; {{$brand[0]['insta_handle']}}</li>
              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Year Established:</strong> &nbsp; {{$brand[0]['established_year']}}</li>
              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Store Carried:</strong> &nbsp; {{$brand[0]['num_store']}}</li>
              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">First Minimum Order:</strong> &nbsp; {{$brand[0]['first_order_min']}}</li>
              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Reorder Minimum:</strong> &nbsp; {{$brand[0]['re_order_min']}}</li>
              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Average Lead Time:</strong> &nbsp; {{$brand[0]['avg_lead_time']}}</li>

          </ul>
                  </div>
              </div>
            </div>
              <div class="col-12 col-xl-4">
                  <div class="card card-plain h-100">
                      <div class="card-header pb-0 p-3">
                          <h6 class="mb-0">Other Information</h6>
                      </div>
                      <div class="card-body p-3">
                          <p>{{$brand[0]['shared_brd_story']}}</p>
                          <ul class="list-group">
                              <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Primary Category:</strong> {{$brand[0]['prim_cat']}}</li>
                              <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Headquartered:</strong> &nbsp; {{$brand[0]['country']}}</li>
                          </ul>
                      </div>
                  </div>
              </div>
          </div>
        </div>
      </div>
    </div>


@include('backend::footer')

