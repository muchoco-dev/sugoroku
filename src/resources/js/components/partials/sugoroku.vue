<template>
<div>
    <div class="row mb-5">
        <div id="sugoroku" class="col-9" style="word-break: break-all">
            <table class="bg-white table table-borderless w-100 board">
                <tr class="horizontal-line">
                    <td v-bind:id="n" v-for="n in col_count">
                        <div v-if="n === 1">
                            Start<br>
                            <span v-for="piece in setPiece(n)">
                                <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece_colors[piece.status]"></i>
                            </span>
                        </div>
                        <div v-else-if="getSpaceName(n)">
                            {{ getSpaceName(n) }}
                            <span v-for="piece in setPiece(n)">
                                <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece_colors[piece.status]"></i>
                            </span>
                        </div>
                        <div v-else>
                            <span v-for="piece in setPiece(n)">
                                <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece_colors[piece.status] "></i>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr class="horizontal-line">
                    <td v-bind:id="board.goal_position">
                        Goal<br>
                        <span v-for="piece in setPiece(board.goal_position)">
                            <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece_colors[piece.status]"></i>
                        </span>
                    </td>
                    <td border="0" v-for="n in (col_count-2)" class="bg-light">
                        &nbsp;
                    </td>
                    <td v-bind:id="col_count+1">
                        {{ getSpaceName(col_count+1) }}
                        <span v-for="piece in setPiece(col_count+1)">
                            <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece_colors[piece.status]"></i>
                        </span>
                    </td>
                </tr>
                <tr class="horizontal-line">
                    <td v-for="n in col_count" v-bind:id="board.goal_position - n">
                        {{ getSpaceName(board.goal_position - n) }}
                        <span v-for="piece in setPiece(board.goal_position - n)">
                            <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece_colors[piece.status]"></i>
                        </span>
                        &nbsp;
                    </td>
                </tr>
            </table>
        </div>
        <div id="members" class="col-2">
            <ul class="list-group">
                <li v-for="member in v_members" class="list-group-item">
                    <i v-if="member.aicon" v-bind:class="'fas fa-2x fa-' + member.aicon"></i>
                    {{ member.name }}
                    <span v-if="member.pivot.go">({{ member.pivot.go }})</span>
                    <i class="fas fa-dice" v-if="canShowDiceImage(member)"></i>
                </li>
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-9">
            <div id="logs" class="border h-100">
                <p v-for="log in logs">{{ log }}</p>
            </div>
        </div>
        <div class="col-2">
            <div id="action">
                <button class="btn btn-success" v-if="canShowStartButton()" @click="start()">ゲームスタート</button>
                <button class="btn btn-primary" v-if="canShowRollDiceButton()" @click="rollDice()">サイコロを振る</button>
                <div class="input-group mt-4" v-if="!is_started">
                    <div class="input-group-prepend">
                        <span class="input-group-text">招待URL</span>
                    </div>
                    <input class="copy form-control bg-white" type="text" v-model="join_url" :data-clipboard-text="join_url" readonly>
                </div>
                <button class="btn btn-outline-danger mt-4" v-if="canShowDeleteRoomButton()" @click="deleteRoom()">削除</button>
            </div>
        </div>
    </div>
</div>
</template>
<script>
export default {
    props: {
        board: Object,
        spaces: [Array, Object],
        room: Object,
        members: Array,
        auth_id: Number,
        const: Object,
        token: String
    },
    data() {
        return {
            col_count: 0,
            piece_icons: ['cat', 'crow', 'frog', 'dragon', 'dog'],
            virus_icon: 'virus',
            piece_colors: {
                1: 'text-success',
                2: 'text-danger',
                3:  ''
            },
            pieces: [],
            piece_positions: {},
            logs: [],
            is_started: false,
            join_url: location.href + '/join',
            v_members: this.members,
            v_spaces: this.spaces,
            next_go: 1,
        }
    },
    created: function () {
        this.col_count = (parseInt(this.board.goal_position) - 2) / 2;
        if (this.room.status === this.const.room_status_busy) {
            this.is_started = true;
        }

        this.resetMembers();
        this.setNextGo();
    },
    mounted: function () {
        window.Echo.private('member-added-channel.' + this.room.id).listen('MemberAdded', response => {
            // response.userId
            // response.roomId
            // これを使ってユーザ名取得&this.v_membersに追加
        });

        window.Echo.private('sugoroku-started-channel.' + this.room.id).listen('SugorokuStarted', response => {
            this.logs.push('ゲームスタート！');
            this.setSpaces();
            this.resetMembers();
        });

        window.Echo.private('dice-rolled-channel.' + this.room.id).listen('DiceRolled', response => {
            this.logs.push(this.getMemberName(response.userId) + 'さんがサイコロをふって' + response.number + '進みました');
            this.resetMembers();
            this.setNextGo();
        });
  },
  methods: {
    getMemberName: function (id) {
        for (let key in this.v_members) {
            if (this.v_members[key]['id'] === id) {
                return this.v_members[key]['name'];
            }
        }
    },
    setSpaces: function () {
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.get('/api/sugoroku/spaces/' + this.room.id, {
            headers: {
                "Content-Type": "application/json"
            }
        }).then(function (response) {
            if (response.data.status === 'success') {
                this.v_spaces = response.data.spaces;
            }
        }.bind(this)).catch(function(error) {
            console.log(error);
        });

    },
    getSpaceName: function (id) { // 特殊マスの名前を返す
        if (this.v_spaces[id]) {
            return this.v_spaces[id].name;
        }
        return ' ';
    },
    setNextGo: function () {
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.get('/api/sugoroku/next_go/' + this.room.id, {
            headers: {
                "Content-Type": "application/json"
            }
        }).then(function (response) {
            if (response.data.status === 'success') {
                this.next_go = response.data.next_go;
            }
        }.bind(this)).catch(function(error) {
            console.log(error);
        });

    },
    resetMembers: function () {
        // メンバー情報及びコマ情報の一括更新
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.get('/api/sugoroku/members/' + this.room.id, {
            headers: {
                "Content-Type": "application/json"
            }
        }).then(function (response) {
            if (response.data.status === 'success') {
                this.v_members = response.data.members;
                this.piece_positions = [];
                let aicon_count = 0;
                let aicon_name = '';
                for (let key in this.v_members) {
                    let position = this.v_members[key]['pivot']['position'];
                    if (!this.piece_positions[position]) {
                        this.piece_positions[position] = [];
                    }

                    if (this.v_members[key]['id'] === this.const.virus_user_id) {
                        aicon_name = this.virus_icon;
                    } else {
                        aicon_name = this.piece_icons[aicon_count];
                        aicon_count++;
                    }

                    this.piece_positions[position].push({
                        user_id: this.v_members[key]['id'],
                        status: this.v_members[key]['pivot']['status'],
                        aicon: aicon_name,
                    });
                    this.v_members[key]['aicon'] = aicon_name;
                }

            }
        }.bind(this)).catch(function(error) {
            console.log(error);
        });

    },
    setPiece: function (position) { // マスにコマを配置する
        return this.piece_positions[position];
    },
    rollDice: function () {
        let min = 1;
        let max = 6;
        let dice = Math.floor( Math.random() * (max + 1 - min) ) + min ;

        this.saveLog(this.const.action_by_dice, this.const.effect_move_forward, dice);
    },
    canShowStartButton: function () {
        if (this.room.owner_id === this.auth_id &&
            this.room.status === this.const.room_status_open) {
        if (!this.is_started) {
          return true;
        }
      }
      return false;
    },
    start: function () {
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.post('/api/sugoroku/start', {
            headers: {
                "Content-Type": "application/json"
            },
            'room_id': this.room.id
        }).then(response => {
            if (response.data.status === 'success') {
                this.is_started = true;
            } else {
                alert(response.data.message);
            }
        }).catch(function(error) {
            console.log(error);
        });
    },
    saveLog: function (action_id, effect_id, effect_num) {
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.post('/api/sugoroku/save_log', {
            headers: {
                "Content-Type": "application/json"
            },
            'room_id': this.room.id,
            'action_id': action_id,
            'effect_id': effect_id,
            'effect_num': effect_num
        }).then(response => {
            if (response.data.status === 'success') {
                // 成功
            } else {
                alert(失敗しました);
            }
        }).catch(function(error) {
            console.log(error.status);
            alert('リクエストに耐えきれませんでした、、、時間を置いて再度お試しください');
        });
    },
    canShowRollDiceButton: function () {
        if (this.is_started) {
            for (let key in this.v_members) {
                if (this.v_members[key]['pivot']['go'] === parseInt(this.next_go) &&
                    this.v_members[key]['id'] === this.auth_id &&
                    this.v_members[key]['pivot']['status'] !== this.const.piece_status_finished) {
                    return true;
                }
            }
        }

        return false;
    },
    canShowDiceImage: function (member) {
        if (this.is_started &&
            member['pivot']['go'] === parseInt(this.next_go) &&
            member['pivot']['status'] !== this.const.piece_status_finished) {

            return true;
        }

        return false;
    },
    canShowDiceImage: function (member) {
        if (this.is_started &&
            member['pivot']['go'] === parseInt(this.next_go) &&
            member['pivot']['status'] !== this.const.piece_status_finished) {

            return true;
        }
        return false;
    },

    canShowDeleteRoomButton: function () {
        if (this.room.owner_id === this.auth_id) {

            let finished_member_count = 0;
            for (let key in this.v_members) {
                if (this.v_members[key]['pivot']['status'] === this.const.piece_status_finished) {
                    finished_member_count++;
                }
            }
            if(!this.is_started ||
                finished_member_count >= this.v_members.length - 1) {
                return true;
            }
        }
        return false;
    },
    deleteRoom: function () {
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.post('/api/sugoroku/delete', {
            headers: {
                "Content-Type": "application/json"
            },
            'room_id': this.room.id,
        }).then(response => {
            if (response.data.status === 'success') {
                // 成功
                window.location.href = '/rooms';
            } else {
                alert(失敗しました);
            }
        }).catch(function(error) {
            console.log(error);
        });
    },
  }
}
</script>
