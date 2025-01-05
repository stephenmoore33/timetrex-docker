// import { createRouter, createWebHistory } from 'vue-router'
import { createRouter, createMemoryHistory } from 'vue-router'

import LegacyView from '@/components/LegacyView';
// import ReportView from '@/components/ReportView';

//const lazy_load_test = () => import(/* webpackChunkName: "dynamic-testview" */'@/components/TTTestView'); // #VUETEST

// Can also import this from another file.
const routes = [
	// { path: '/test', name: 'test', component: lazy_load_test, props:true }, // #VUETEST Lazy loaded, so not loaded normally. Only when used with `VueRouter.push('test')` or via dev tools.
	// { path: '/view/:viewId', name: 'view', component: LegacyView, props:true },
	// { path: '/report/:viewId', name: 'report', component: ReportView, props:true },
	// { path: '/report/:reportId', name: 'report', component: LegacyView },
	// { path: '/wizard/:wizardId', name: 'wizard', component: LegacyView },
	{ path: '/:pathMatch(.*)*', name: 'catch-all', component: LegacyView },
	// { path: '/#!m=Login', name: 'not-found', component: LegacyView },
	// { path: '/#!m=:viewId&*:restOf(.*)', name: 'not-found', component: LegacyView },

];

const main_ui_router = createRouter({
	// history: createWebHistory(),
	history: createMemoryHistory(),
	routes,
});

export default main_ui_router;
