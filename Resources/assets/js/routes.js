const UserMaster = () => import('../components/User/Master.vue')
const UserForm = () => import('../components/User/Form.vue')

export default class routes{
    constructor(Meta) {
        this.meta = Meta;
    }

    route(){
        return [
            {
                path: 'user/master',
                name: 'user-master',
                components : {
                    content : UserMaster,
                },
                props: { content: true },
                meta: {...this.meta, title_dashboard: 'User'}
            },
            {
                path: 'user/form',
                name: 'user-form',
                components : {
                    content : UserForm,
                },
                meta: {...this.meta, title_dashboard: 'User'}
            },
        ]
    }
}