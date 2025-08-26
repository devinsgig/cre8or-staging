<div class="modal-backdrop-sidebar"></div>
<aside class="aside" id="leftSidebarMenu">
  <div class="aside-menu-top">
    <div class="aside-menu-logo">
      <a href="{{route('admin.dashboard.index')}}">
        <img src="{{setting('site.logo')}}"/>
      </a>
    </div>
    <a title="{{__('logout')}}" href="{{route('admin.auth.logout')}}"><span class="material-symbols-outlined notranslate"> logout </span></a>
  </div>
  <div class="aside-menu">
    <ul class="aside-menu-list" id="asideMenuList">
      <li class="@isRouter(admin.dashboard.index) active @endIsRouter">
        <a href="{{route('admin.dashboard.index')}}">
          <span class="material-symbols-outlined notranslate"> space_dashboard </span>
          <span class="aside-menu-item-label">{{__('Dashboard')}}</span>
        </a>
      </li>

      <li>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseSiteInformation" aria-expanded="false" aria-controls="collapseSiteInformation">
            <span class="material-symbols-outlined notranslate"> settings </span>
            <span class="aside-menu-item-label">{{__('Site Settings')}}</span>
            <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
        </a>
        <ul class="aside-menu-list-child collapse" id="collapseSiteInformation" data-bs-parent="#asideMenuList">
            @hasPermission('admin.setting.site')
            <li class="@isRouter(admin.setting.site) active @endIsRouter">
              <a href="{{route('admin.setting.site')}}">
                <span class="aside-menu-item-label">{{__('Site Information')}}</span>
              </a>
            </li>
            @endHasPermission

            @hasPermission('admin.setting.general')
            <li class="@isRouter(admin.setting.general) active @endIsRouter">
              <div class="aside-menu-list-child-item-has-child">
                <a href="{{route('admin.setting.general')}}">
                  <span class="aside-menu-item-label">{{__('General Configuration')}}</span>
                </a>
                <div data-bs-toggle="collapse" data-bs-target="#collapseGeneralConfiguration" aria-expanded="true" aria-controls="collapseGeneralConfiguration">
                  <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
                </div>
              </div>
              <ul id="collapseGeneralConfiguration" class="aside-menu-list-child-item-list collapse">
                @foreach ($generalSettings->groupSubs as $key => $groupSub)
                  <li>
                    <a href="{{route('admin.setting.general') . '#general' . $groupSub->id}}">
                      {{__($groupSub->name)}}
                    </a>
                  </li>
                @endforeach
              </ul>
            </li>
            @endHasPermission

            @hasPermission('admin.mail.manage')
            <li class="@isRouter(admin.mail.index,admin.mail.template) active @endIsRouter">
                <a href="{{route('admin.mail.index')}}">
                    <span class="aside-menu-item-label">{{__('Mail Templates')}}</span>
                </a>
            </li>
            @endHasPermission

            @hasPermission('admin.menu.manage')
              <li class="@isRouter(admin.menu.index) active @endIsRouter">
                <a href="{{route('admin.menu.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Menus')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.language.manage')
              <li class="@isRouter(admin.language.index,admin.language.edit_phrase) active @endIsRouter">
                <a href="{{route('admin.language.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Languages')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.openid.manage')
              <li class="@isRouter(admin.openid.index,admin.openid.create) active @endIsRouter">
                <a href="{{route('admin.openid.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage OpenID')}}</span>
                </a>
              </li>
            @endHasPermission
            
            @hasPermission('admin.gender.manage')
              <li class="@isRouter(admin.gender.index) active @endIsRouter">
                <a href="{{route('admin.gender.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Genders')}}</span>
                </a>
              </li>
            @endHasPermission
            
            @isSuperAdmin
              <li class="@isRouter(admin.role.index,admin.role.permission) active @endIsRouter">
                <a href="{{route('admin.role.index')}}">
                  <span class="aside-menu-item-label">{{__('Manage User Roles')}}</span>
                </a>
              </li>
            @endIsSuperAdmin

            @hasPermission('admin.theme.manage')
              <li class="@isRouter(admin.theme.index,admin.theme.setting) active @endIsRouter">
                <a href="{{route('admin.theme.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Themes')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.layout.manage')
              <li class="@isRouter(admin.layout.index) active @endIsRouter">
                <a href="{{route('admin.layout.index')}}">
                    <span class="aside-menu-item-label">{{__('Layout Editor')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.currency.manage')
              <li class="@isRouter(admin.currency.index) active @endIsRouter">
                <a href="{{route('admin.currency.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Currencies')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.gateway.manage')
              <li class="@isRouter(admin.gateway.index) active @endIsRouter">
                <a href="{{route('admin.gateway.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Gateways')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.country.manage')
              <li class="@isRouter(admin.country.index) active @endIsRouter">
                <a href="{{route('admin.country.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Countries')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.sms_provider.manage')
              <li class="@isRouter(admin.sms_provider.index) active @endIsRouter">
                <a href="{{route('admin.sms_provider.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Sms Providers')}}</span>
                </a>
              </li>
            @endHasPermission
        </ul>
      </li>

      <li>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseAppInformation" aria-expanded="false" aria-controls="collapseAppInformation">
            <span class="material-symbols-outlined notranslate"> phone_android </span>
            <span class="aside-menu-item-label">{{__('Mobile Application')}}</span>
            <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
        </a>
        <ul class="aside-menu-list-child collapse" id="collapseAppInformation" data-bs-parent="#asideMenuList">
            @hasPermission('admin.setting.mobile_general')
            <li class="@isRouter(admin.setting.mobile_general) active @endIsRouter">
              <a href="{{route('admin.setting.mobile_general')}}">
                <span class="aside-menu-item-label">{{__('General Configuration')}}</span>
              </a>
            </li>
            @endHasPermission
            @hasPermission('admin.mobile.broadcast_message')
            <li class="@isRouter(admin.mobile.broadcast_message) active @endIsRouter">
              <a href="{{route('admin.mobile.broadcast_message')}}">
                <span class="aside-menu-item-label">{{__('Broadcast Message')}}</span>
              </a>
            </li>
            @endHasPermission
        </ul>
      </li>

      <li>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystemSetting" aria-expanded="false" aria-controls="collapseSystemSetting">
            <span class="material-symbols-outlined notranslate"> computer </span>
            <span class="aside-menu-item-label">{{__('System Setting')}}</span>
            <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
        </a>
        <ul class="aside-menu-list-child collapse" id="collapseSystemSetting" data-bs-parent="#asideMenuList">
          @hasPermission('admin.cache.setting')
            <li class="@isRouter(admin.cache.index) active @endIsRouter">
              <a href="{{route('admin.cache.index')}}">
                  <span class="aside-menu-item-label">{{__('Cache Setting')}}</span>
              </a>
            </li>
          @endHasPermission
          
          @hasPermission('admin.storage.manage')
          <li class="@isRouter(admin.storage.index,admin.storage.edit) active @endIsRouter">
            <a href="{{route('admin.storage.index')}}">
                <span class="aside-menu-item-label">{{__('Storage System')}}</span>
            </a>
          </li>
        @endHasPermission

          @hasPermission('admin.task.manage')
            <li class="@isRouter(admin.task.index) active @endIsRouter">
              <a href="{{route('admin.task.index')}}">
                  <span class="aside-menu-item-label">{{__('Tasks')}}</span>
              </a>
            </li>
          @endHasPermission

          @hasPermission('admin.log.manage')
            <li class="@isRouter(admin.log.index) active @endIsRouter">
              <a href="{{route('admin.log.index')}}">
                  <span class="aside-menu-item-label">{{__('System Log')}}</span>
              </a>
            </li>
          @endHasPermission

        </ul>
      </li>

      <li>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseContentManager" aria-expanded="false" aria-controls="collapseContentManager">
            <span class="material-symbols-outlined notranslate"> content_copy </span>
            <span class="aside-menu-item-label">{{__('Content Manager')}}</span>
            <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
        </a>
        <ul class="aside-menu-list-child collapse" id="collapseContentManager" data-bs-parent="#asideMenuList">
          @hasPermission('admin.page.manage')
            <li class="@isRouter(admin.page.index,admin.page.create) active @endIsRouter">
              <a href="{{route('admin.page.index')}}">
                  <span class="aside-menu-item-label">{{__('Static Pages')}}</span>
              </a>
            </li>
          @endHasPermission

          @hasPermission('admin.hashtag.manage')
            <li class="@isRouter(admin.hashtag.index) active @endIsRouter">
              <a href="{{route('admin.hashtag.index')}}">
                  <span class="aside-menu-item-label">{{__('Hashtags')}}</span>
              </a>
            </li>
          @endHasPermission

          @hasPermission('admin.user.manage')
            <li class="@isRouter(admin.user.index,admin.user.create) active @endIsRouter">
              <a href="{{route('admin.user.index')}}">
                  <span class="aside-menu-item-label">{{__('Users')}}</span>
              </a>
            </li>
          @endHasPermission
          
          @hasPermission('admin.report.manage')
          <li class="@isRouter(admin.report.index) active @endIsRouter">
              <a href="{{route('admin.report.index')}}">
                  <span class="aside-menu-item-label">{{__('Reports')}}</span>
              </a>
          </li>
          @endHasPermission

          @hasPermission('admin.report.manage_category')
          <li class="@isRouter(admin.report.category) active @endIsRouter">
              <a href="{{route('admin.report.category')}}">
                  <span class="aside-menu-item-label">{{__('Report Categories')}}</span>
              </a>
          </li>
          @endHasPermission

          @if (env('ADMIN_SHOW_MANAGE_MESSAGE'))
            @hasPermission('admin.chat.manage')
            <li class="@isRouter(admin.chat.index,admin.chat.detail) active @endIsRouter">
                <a href="{{route('admin.chat.index')}}">
                    <span class="aside-menu-item-label">{{__('Messages')}}</span>
                </a>
            </li>
            @endHasPermission
          @endif

          @hasPermission('admin.user_verify.request_manage')
          <li class="@isRouter(admin.user_verify.index) active @endIsRouter">
              <a href="{{route('admin.user_verify.index')}}">
                  <span class="aside-menu-item-label">{{__('Verification Requests')}}</span>
              </a>
          </li>
          @endHasPermission

          @hasPermission('admin.content_warning.manage_category')
          <li class="@isRouter(admin.content_warning.category) active @endIsRouter">
            <a href="{{route('admin.content_warning.category')}}">
                <span class="aside-menu-item-label">{{__('Content Warning Categories')}}</span>
            </a>
          </li>
          @endHasPermission
        </ul>
      </li>
      <li>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseStory" aria-expanded="false" aria-controls="collapseStory">
            <span class="material-symbols-outlined notranslate"> width_normal </span>
            <span class="aside-menu-item-label">{{__('Story')}}</span>
            <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
        </a>
        <ul class="aside-menu-list-child collapse" id="collapseStory" data-bs-parent="#asideMenuList">
          @hasPermission('admin.story.background_manage')
            <li class="@isRouter(admin.story.background.index) active @endIsRouter">
              <a href="{{route('admin.story.background.index')}}">
                  <span class="aside-menu-item-label">{{__('Backgrounds')}}</span>
              </a>
            </li>
          @endHasPermission
          @hasPermission('admin.story.song_manage')
            <li class="@isRouter(admin.story.song.index) active @endIsRouter">
              <a href="{{route('admin.story.song.index')}}">
                  <span class="aside-menu-item-label">{{__('Songs')}}</span>
              </a>
            </li>
          @endHasPermission
        </ul>
      </li>
      <li>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseBilling" aria-expanded="false" aria-controls="collapseBilling">
            <span class="material-symbols-outlined notranslate"> payments </span>
            <span class="aside-menu-item-label">{{__('Billings')}}</span>
            <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
        </a>
        <ul class="aside-menu-list-child collapse" id="collapseBilling" data-bs-parent="#asideMenuList">
          @hasPermission('admin.subscription.manage')
            <li class="@isRouter(admin.subscription.index,admin.subscription.detail) active @endIsRouter">
              <a href="{{route('admin.subscription.index')}}">
                  <span class="aside-menu-item-label">{{__('Subscriptions')}}</span>
              </a>
            </li>
          @endHasPermission
        </ul>
      </li>
      <li>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseWallet" aria-expanded="false" aria-controls="collapseWallet">
            <span class="material-symbols-outlined notranslate"> account_balance </span>
            <span class="aside-menu-item-label">{{__('eWallet')}}</span>
            <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
        </a>
        <ul class="aside-menu-list-child collapse" id="collapseWallet" data-bs-parent="#asideMenuList">
          @hasPermission('admin.wallet.manage')
            <li class="@isRouter(admin.wallet.index,admin.wallet.transactions) active @endIsRouter">
              <a href="{{route('admin.wallet.index')}}">
                  <span class="aside-menu-item-label">{{__('Wallets')}}</span>
              </a>
            </li>
          @endHasPermission

          @hasPermission('admin.wallet.package_manage')
            <li class="@isRouter(admin.wallet.package.index) active @endIsRouter">
              <a href="{{route('admin.wallet.package.index')}}">
                  <span class="aside-menu-item-label">{{__('Deposit Packages')}}</span>
              </a>
            </li>
          @endHasPermission

          @hasPermission('admin.wallet.withdraw_manage')
            <li class="@isRouter(admin.wallet.withdraw.index) active @endIsRouter">
              <a href="{{route('admin.wallet.withdraw.index')}}">
                  <span class="aside-menu-item-label">{{__('Transfer Fund Requests')}}</span>
              </a>
            </li>
          @endHasPermission

          @hasPermission('admin.wallet.mass_funds')
            <li class="@isRouter(admin.wallet.fund.index) active @endIsRouter">
              <a href="{{route('admin.wallet.fund.index')}}">
                  <span class="aside-menu-item-label">{{__('Transfer Mass Funds')}}</span>
              </a>
            </li>
          @endHasPermission

          @hasPermission('admin.wallet.billing_activity')
            <li class="@isRouter(admin.wallet.billing_activity) active @endIsRouter">
              <a href="{{route('admin.wallet.billing_activity')}}">
                <span class="aside-menu-item-label">{{__("System's billing activities")}}</span>
              </a>
            </li>
          @endHasPermission
        </ul>
        <li>
          <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseUserPage" aria-expanded="false" aria-controls="collapseUserPage">
              <span class="material-symbols-outlined notranslate"> account_box </span>
              <span class="aside-menu-item-label">{{__('Business Pages')}}</span>
              <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
          </a>
          <ul class="aside-menu-list-child collapse" id="collapseUserPage" data-bs-parent="#asideMenuList">
            @hasPermission('admin.user_page.manage')
              <li class="@isRouter(admin.user_page.index,admin.user_page.edit,admin.user_page.admin_manage) active @endIsRouter">
                <a href="{{route('admin.user_page.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Pages')}}</span>
                </a>
              </li>
            @endHasPermission
  
            {{-- @hasPermission('admin.user_page.manage_categories')
              <li class="@isRouter(admin.user_page.category.index) active @endIsRouter">
                <a href="{{route('admin.user_page.category.index')}}">
                    <span class="aside-menu-item-label">{{__('Page Categories')}}</span>
                </a>
              </li>
            @endHasPermission --}}

            @hasPermission('admin.user_page.manage_verifes')
              <li class="@isRouter(admin.user_page.verify.index) active @endIsRouter">
                <a href="{{route('admin.user_page.verify.index')}}">
                    <span class="aside-menu-item-label">{{__('Page Verification Requests')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.user_page.manage_packages')
              <li class="@isRouter(admin.user_page.feature_package.index) active @endIsRouter">
                <a href="{{route('admin.user_page.feature_package.index')}}">
                    <span class="aside-menu-item-label">{{__('Page Feature Packages')}}</span>
                </a>
              </li>
            @endHasPermission
          </ul>
        </li>
        <li>
          <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseAdvertising" aria-expanded="false" aria-controls="collapseAdvertising">
              <span class="material-symbols-outlined notranslate"> ads_click </span>
              <span class="aside-menu-item-label">{{__('Advertising')}}</span>
              <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
          </a>
          <ul class="aside-menu-list-child collapse" id="collapseAdvertising" data-bs-parent="#asideMenuList">
            @hasPermission('admin.advertising.manage')
              <li class="@isRouter(admin.advertising.index,admin.advertising.detail) active @endIsRouter">
                <a href="{{route('admin.advertising.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Advertisings')}}</span>
                </a>
              </li>
            @endHasPermission
          </ul>
        </li>
        <li>
          <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseSubscription" aria-expanded="false" aria-controls="collapseSubscription">
              <span class="material-symbols-outlined notranslate"> subscriptions </span>
              <span class="aside-menu-item-label">{{__('Membership')}}</span>
              <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
          </a>
          <ul class="aside-menu-list-child collapse" id="collapseSubscription" data-bs-parent="#asideMenuList">
            @hasPermission('admin.user_subscription.manage_package')
              <li class="@isRouter(admin.user_subscription.index,admin.user_subscription.plan.index) active @endIsRouter">
                <a href="{{route('admin.user_subscription.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Packages')}}</span>
                </a>
              </li>
            @endHasPermission
            @hasPermission('admin.user_subscription.pricing_table')
              <li class="@isRouter(admin.user_subscription.pricing_table.index) active @endIsRouter">
                <a href="{{route('admin.user_subscription.pricing_table.index')}}">
                    <span class="aside-menu-item-label">{{__('Customize Pricing Plans')}}</span>
                </a>
              </li>
            @endHasPermission
          </ul>
        </li>
        <li>
          <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseGroup" aria-expanded="false" aria-controls="collapseGroup">
              <span class="material-symbols-outlined notranslate"> subscriptions </span>
              <span class="aside-menu-item-label">{{__('Groups')}}</span>
              <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
          </a>
          <ul class="aside-menu-list-child collapse" id="collapseGroup" data-bs-parent="#asideMenuList">
            @hasPermission('admin.group.manage')
              <li class="@isRouter(admin.group.index) active @endIsRouter">
                <a href="{{route('admin.group.index')}}">
                    <span class="aside-menu-item-label">{{__('Manage Groups')}}</span>
                </a>
              </li>
            @endHasPermission
            @hasPermission('admin.group.manage_categories')
              <li class="@isRouter(admin.group.category.index) active @endIsRouter">
                <a href="{{route('admin.group.category.index')}}">
                  <span class="aside-menu-item-label">{{__('Group Categories')}}</span>
                </a>
            </li>
            @endHasPermission
          </ul>
        </li>
        <li>
          <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseVibb" aria-expanded="false" aria-controls="collapseVibb">
              <span class="material-symbols-outlined notranslate"> video_library </span>
              <span class="aside-menu-item-label">{{__('Vibb')}}</span>
              <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
          </a>
          <ul class="aside-menu-list-child collapse" id="collapseVibb" data-bs-parent="#asideMenuList">
            @hasPermission('admin.vibb.song_manage')
              <li class="@isRouter(admin.vibb.song.index) active @endIsRouter">
                <a href="{{route('admin.vibb.song.index')}}">
                    <span class="aside-menu-item-label">{{__('Songs')}}</span>
                </a>
              </li>
            @endHasPermission
          </ul>
        </li>
        <li>
          <a href="#" data-bs-toggle="collapse" data-bs-target="#collapsePaidContent" aria-expanded="false" aria-controls="collapsePaidContent">
              <span class="material-symbols-outlined notranslate"> paid </span>
              <span class="aside-menu-item-label">{{__('Paid Content')}}</span>
              <span class="material-symbols-outlined notranslate"> arrow_drop_down </span>
          </a>
          <ul class="aside-menu-list-child collapse" id="collapsePaidContent" data-bs-parent="#asideMenuList">
            @hasPermission('admin.paid_content.packages_manage')
              <li class="@isRouter(admin.paid_content.subscription.package) active @endIsRouter">
                <a href="{{route('admin.paid_content.subscription.package')}}">
                    <span class="aside-menu-item-label">{{__('Subscriptions Packages')}}</span>
                </a>
              </li>
            @endHasPermission

            @hasPermission('admin.paid_content.tips_manage')
              <li class="@isRouter(admin.paid_content.tip.package) active @endIsRouter">
                <a href="{{route('admin.paid_content.tip.package')}}">
                    <span class="aside-menu-item-label">{{__('Tips Packages')}}</span>
                </a>
              </li>
            @endHasPermission
          </ul>
        </li>
      </li>
    </ul>
  </div>
  <div class="aside-menu-bottom">
    <span>{{__('Language')}}: </span>
    <a href="javascript:void(0);" id="showLanguageModal">{{$languagesGlobal[$languageCurrent]}}</a>
  </div>
</aside>
@push('scripts-body')
<script>
    adminCore.handleSidebarMenu();
    adminCore.showLanguageModal();
</script>
@endpush