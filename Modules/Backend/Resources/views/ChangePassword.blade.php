@include('backend::header')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-6">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Change Password</h6>
          </div>
        </div>
        <div class="card-body px-0 pb-4">

              <form role="form" method="post" action="">
                  @csrf
                  @if(session()->has('errors'))
                      <div class="alert alert-danger" role="alert" style="width: 73%;margin: 0 auto 10px;color: white;">
                          {{ session()->get('errors') }}
                      </div>
                  @endif
                  @if(session()->has('success'))
                      <div class="alert alert-success" role="alert" style="width: 73%;margin: 0 auto 10px;color: white;">
                          {{ session()->get('success') }}
                      </div>
                  @endif
                  <div class="input-group input-group-outline mb-3" style="width: 90%;margin: 0 auto">
                      <label class="form-label">Old Password</label>
                      <input type="password" name="old_password" class="form-control">
                  </div>
                  <div class="input-group input-group-outline mb-3" style="width: 90%;margin: 0 auto">
                      <label class="form-label">New Password</label>
                      <input type="password" name="new_password" class="form-control">
                  </div>
                  <div class="input-group input-group-outline mb-3" style="width: 90%;margin: 0 auto">
                      <label class="form-label">Confirm Password</label>
                      <input type="password" name="confirm_password" class="form-control">
                  </div>

                  <div class="text-center" style="width: 30%;margin: 0 auto">
                      <button type="submit" class="btn btn-lg bg-gradient-primary btn-lg w-100 mt-4 mb-0">Submit</button>
                  </div>
              </form>

        </div>
      </div>
    </div>
  </div>
</div>

@include('backend::footer')
