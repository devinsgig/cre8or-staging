<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@if (trim($__env->yieldContent('template_title')))@yield('template_title') | @endif {{ __('Site Install') }}</title>
        <link href="{{ asset('install/css/main.css') }}" rel="stylesheet"/>
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/@mdi/font{{'@'.config('shaun_core.materialicon.version')}}/css/materialdesignicons.min.css">
        @yield('style')
        <script>
            window.Laravel = <?php echo json_encode([
                'csrfToken' => csrf_token(),
            ]); ?>
        </script>
    </head>
    <body>
        <div class="flex justify-center items-center min-h-screen">
            <div class="mx-auto p-10 border max-w-3xl shadow-lg rounded-md bg-white">
                <h1 class="mb-10 text-3xl font-bold text-center">{{__('Welcome to the ShaunSocial Installation')}}</h1>
                <div class="flex space-x-4 flex-row-reverse space-x-reverse m-auto mb-6 text-center">                                                           
                    <div class="flex-1 border-t-4 pt-2 border-gray-200 @isRouter(install.final) border-base-blue active @endIsRouter">
                        <p class="font-semibold text-gray-300 @isRouter(install.final) text-base-blue @endIsRouter">{{__('Installation Finished')}}</p>
                    </div>
                    <div class="flex-1 border-t-4 pt-2 border-gray-200 @isRouter(install.admin) border-base-blue active @endIsRouter">
                        <p class="font-semibold text-gray-300 @isRouter(install.admin) text-base-blue @endIsRouter">{{__('Create Admin Account')}}</p>
                    </div>
                    <div class="flex-1 border-t-4 pt-2 border-gray-200 @isRouter(install.setting) border-base-blue active @endIsRouter">
                        <p class="font-semibold text-gray-300 @isRouter(install.setting) text-base-blue @endIsRouter">{{__('Site Info')}}</p>
                    </div>
                    <div class="flex-1 border-t-4 pt-2 border-gray-200 @isRouter(install.environmentWizard) border-base-blue active @endIsRouter">
                        <p class="font-semibold text-gray-300 @isRouter(install.environmentWizard) text-base-blue @endIsRouter">{{__('Environment Settings')}}</p>
                    </div>
                    <div class="flex-1 border-t-4 pt-2 border-gray-200 @isRouter(install.requirements) border-base-blue active @endIsRouter">
                        <p class="font-semibold text-gray-300 @isRouter(install.requirements) text-base-blue @endIsRouter">{{__('Server Requirements')}}</p>
                    </div>           
                </div>
                <div class="text-sm">
                    @if (session('message'))
                        <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded relative mb-5 max-w-lg mx-auto text-center">
                            <strong>
                                @if(is_array(session('message')))
                                    {{ session('message')['message'] }}
                                @else
                                    {{ session('message') }}
                                @endif
                            </strong>
                        </div>
                    @endif
                    @if(session()->has('errors'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-5 max-w-lg mx-auto text-center" id="error_alert">
                            <button type="button" class="absolute top-0 right-1" id="close_alert" data-dismiss="alert" aria-hidden="true">
                                 <i class="mdi mdi-close-circle"></i>
                            </button>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('container')
                </div>
            </div>
        </div>
        @yield('scripts')
        <script type="text/javascript">
            var x = document.getElementById('error_alert');
            var y = document.getElementById('close_alert');

            if (y != null){
                y.onclick = function() {
                    x.style.display = "none";
                };
            }
        </script>
    </body>
</html>
