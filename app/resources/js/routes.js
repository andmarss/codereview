import Admin from './components/Admin';
import User from './components/User';

export default [
    {
        name: 'admin',
        path: '/admin',
        component: Admin
    },
    {
        name: 'view-user',
        path: '/admin/user/:id',
        component: User,
        props: route => {
            return {
                id: route.params.id,
            }
        }
    }
];
