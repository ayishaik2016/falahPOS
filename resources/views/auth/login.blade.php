@extends('layouts.guest')
@section('title', __('auth.login'))

@section('container')

	<!--wrapper-->
	<div class="wrapper">
		<div class="section-authentication-cover">
			<div class="">
				<div class="row g-0">

					<div class="col-12 col-xl-7 col-xxl-8 auth-cover-left align-items-center justify-content-center d-none d-xl-flex">
						<svg width="100%" height="100%" viewBox="0 0 1400 1050" xmlns="http://www.w3.org/2000/svg">

							<defs>
								<!-- Background Gradient -->
								<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
								<stop offset="0%" stop-color="#0f172a"/>
								<stop offset="100%" stop-color="#1e293b"/>
								</linearGradient>

								<!-- Brand Gradient -->
								<linearGradient id="brand" x1="0" y1="0" x2="1" y2="1">
								<stop offset="0%" stop-color="#2b6cb0"/>
								<stop offset="100%" stop-color="#2bb673"/>
								</linearGradient>

								<!-- Glow -->
								<filter id="blur">
								<feGaussianBlur stdDeviation="60" />
								</filter>
							</defs>

							<!-- Background -->
							<rect width="1400" height="1050" fill="url(#bg)" />

							<!-- Soft Gradient Blobs -->
							<circle cx="1100" cy="150" r="200" fill="#2bb673" opacity="0.15" filter="url(#blur)" />
							<circle cx="200" cy="600" r="250" fill="#2b6cb0" opacity="0.15" filter="url(#blur)" />

							<!-- Logo Icon -->
							<!-- <g transform="translate(250,200)">
								<rect x="0" y="0" width="160" height="160" rx="40"
										fill="#0f172a" />

								<defs>
									<linearGradient id="iconGradient" x1="0" y1="0" x2="1" y2="1">
									<stop offset="0%" stop-color="#3b82f6"/>
									<stop offset="100%" stop-color="#34d399"/>
									</linearGradient>
								</defs>

								<path d="M40 30 
										Q40 20 55 20 
										L110 20 
										Q125 20 125 35 
										L125 115 
										L110 105 
										L95 115 
										L80 105 
										L65 115 
										L50 105 
										L40 115 Z"
										fill="url(#iconGradient)" />

								<rect x="60" y="40" width="45" height="6" rx="3" fill="#ffffff" opacity="0.9"/>
								<rect x="60" y="60" width="35" height="6" rx="3" fill="#ffffff" opacity="0.8"/>
								<rect x="60" y="80" width="40" height="6" rx="3" fill="#ffffff" opacity="0.7"/>

								<rect x="55" y="105" width="6" height="10" rx="2" fill="#ffffff"/>
								<rect x="70" y="105" width="6" height="10" rx="2" fill="#ffffff"/>
								<rect x="85" y="105" width="6" height="10" rx="2" fill="#ffffff"/>
								<rect x="100" y="105" width="6" height="10" rx="2" fill="#ffffff"/>
							</g> -->
							<image href="{{ url('/app/getimage/' . app('site')['colored_logo']) }}" x="170" y="220" width="300" height="200" />

							<!-- Logo Text -->
							<text x="420" y="330"
									font-family="Poppins, Inter, Arial, sans-serif"
									font-size="100"
									font-weight="600"
									fill="#ffffff">
								Falaah<tspan fill="#2bb673">POS</tspan>
							</text>

							<!-- Tagline -->
							<text x="420" y="380"
									font-family="Poppins, Inter, Arial, sans-serif"
									font-size="30"
									fill="#94a3b8">
								Smart Billing & POS System
							</text>
						</svg>
					</div>

					<div class="col-12 col-xl-5 col-xxl-4 auth-cover-right align-items-center justify-content-center">
						<div class="card rounded-0 m-3 shadow-none bg-transparent mb-0">
							@if(config('demo.enabled'))
						<div class="position-absolute top-0 end-0 mt-3 me-3">
					      <div class="d-grid">
					        <a href="https://codecanyon.net/item/delta/51635135" target="_blank" class="btn btn-success btn-sm px-4">Buy Now</a>
					      </div>
					    </div>
					    @endif
							<div class="card-body p-sm-5">

								@include('layouts.session')

								<div class="">
									<div class="mb-3 text-center">
										<img src={{ url("/app/getimage/" . app('site')['colored_logo']) }} width="200" alt="">
									</div>
									<div class="text-center mb-4">
										<h5 class="">{{ app('site')['name'] }}</h5>
										<p class="mb-0">{{ __('auth.login_to_account') }}</p>
									</div>
									<div class="form-body">
										<form class="row g-3" id="loginForm" action="{{ route('login') }}" enctype="multipart/form-data">
											{{-- CSRF Protection --}}
                        					@csrf
                        					@method('POST')

											<div class="col-12">
												<x-label for="email" name="{{ __('app.email') }}"/>
												<x-input placeholder="Enter Email" id="email" name="email" type='email' :required="true" :autofocus="true" :autocomplete='true' />
											</div>

											<div class="col-12">
												<x-label for="password" name="{{ __('app.password') }}"/>
												<div class="input-group" id="show_hide_password">
													<x-input placeholder="Enter Password" id="password" name="password" type='password' :required="true"/>
													<a href="javascript:;" class="input-group-text bg-transparent"><i class="bx bx-hide"></i></a>

												</div>
											</div>
											<div class="col-md-6">
												<x-radio-block id="remember" boxName="remember" text="{{ __('auth.remember_me') }}" parentDivClass='form-switch'/>
											</div>
											<div class="col-md-6 text-end">
												<x-anchor-tag href="{{ route('password.request') }}" text="{{ __('auth.forgot_password') }}" />
											</div>
											<div class="col-12">
												<div class="d-grid">
													<x-button type="submit" class="primary" text="{{ __('app.sign_in') }}" />
												</div>
											</div>
											@if(false)
											<div class="col-12">
												<div class="text-center ">
													<p class="mb-0">{{ __('auth.dont_have_account') }}
														<x-anchor-tag href="{{ route('register') }}" text="Sign up here" />
													</p>
												</div>
											</div>
											@endif

											<div class="col-12">
												<div class="text-center ">
													<x-flag-toggle justLinks='true'/>
												</div>
											</div>

                                            @php
                                                $appVersion = getAppVersion();
                                                $dbVersion = getDatabaseMigrationAppVersion();
                                            @endphp

											<div class="text-center">
												<span>Version: {{ $appVersion }}</span>
											</div>


                                            @if($appVersion != $dbVersion)
                                            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                                <div class="text-white">
                                                    Version Mismatch!!<br>
                                                    <small>
                                                        App Version: {{ $appVersion }},
                                                        Database Version: {{ $dbVersion }}
                                                    </small>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                            @endif


											@include('auth.demo-login')

										</form>
									</div>

								</div>
							</div>
						</div>
					</div>

				</div>
				<!--end row-->
			</div>
		</div>
	</div>
	<!--end wrapper-->

@endsection

@section('js')
<!-- Login page -->
<script src="{{ versionedAsset('custom/js/login.js') }}"></script>
@if(config('demo.enabled'))
<script src="{{ versionedAsset('custom/js/demo-login.js') }}"></script>
@endif
@endsection
