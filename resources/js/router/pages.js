import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import ServerLayout from '@/pages/ServerLayout.vue';

export default [
    {
        path: '/pages/:tab?',
        name: 'user_pages',
        component: ServerLayout,
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Pages')) }
    },
    {
        path: '/pages/create',
        name: 'user_pages_create',
        component: () => import('@/pages/user_pages/UserPagesCreate.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Create New Page')) }
    },
    {
        path: '/pages/reports',
        name: 'user_pages_reports',
        component: () => import('@/pages/user_pages/UserPagesReport.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Report')) },
        children: [
            {
                path: '',
                name: 'user_pages_overview',
                props: true,
                component: () => import('@/pages/user_pages/UserPagesReportOverview.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Overview')) },
            },
            {
                path: 'manage_users',
                name: 'user_pages_manage_users',
                props: true,
                component: () => import('@/pages/user_pages/UserPagesManageUsers.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Manage Users')) },
            },
            {
                path: 'notify_settings',
                name: 'user_pages_notify_settings',
                props: true,
                component: () => import('@/pages/user_pages/UserPagesNotifySettings.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Manage Users')) },
            }
        ]
    },
    {
        path: '/pages/feature',
        name: 'user_pages_feature',
        component: () => import('@/pages/user_pages/UserPagesFeature.vue'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Feature Page')) }
    }
]