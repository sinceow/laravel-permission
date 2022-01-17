<template>
    <div class="page-container">
        <newbie-table :filter-form="false" title="新增角色类型，设置角色权限，用于分配管理员权限和工作" ref="table" :columns="getColumns"
                      :page="false" :fetched="fetched" url="api/manager/role">
            <div slot="append">
                <a-button icon="plus" type="primary" @click="onEdit()">新增角色</a-button>
            </div>
        </newbie-table>

        <newbie-drawer ref="drawer" title="编辑角色" name="role-edit"
                       :params="{id: currentRole.id}"></newbie-drawer>

    </div>
</template>
<script>
export default {
    data: function () {
        return {
            currentRole: {}
        };
    },
    methods: {
        fetched: function (res) {
            return {items: res.result};
        },
        getColumns: function () {
            var _self = this;
            return [
                {
                    title: "角色名称",
                    dataIndex: "name",
                    width: 200,
                }, {
                    title: "是否启用",
                    width: 100,
                    key: "is_active",
                    customRender: function (text, record, index) {
                        return UTILS.createTableActions(_self, {
                            type: "span",
                            name: text.is_active ? '是' : '否'
                        });
                    }
                }, {
                    title: "操作",
                    width: 140,
                    customRender: function (text, record, index) {

                        if (['admin', 'campus'].indexOf(text.key) === -1) {
                            return UTILS.createTableActions(_self, [{
                                name: '编辑',
                                props: {
                                    icon: "edit",
                                    size: "small",
                                    type: "primary"
                                },
                                action: function () {
                                    _self.$set(_self, 'currentRole', text)
                                    _self.onEdit();
                                }
                            }]);
                        }

                        return '';
                    }
                }]
        },
        onEdit: function () {
            this.$refs.drawer.open();
        }
    }
}
</script>
