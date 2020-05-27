<template>
<div>
    <div class="row mb-5">
        <div id="sugoroku" class="col-9">
            <table class="bg-white table table-borderless w-100">
                <tr>
                    <td v-bind:id="n" v-for="n in col_count">
                        <div v-if="n === 1">
                            Start
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
                <tr>
                    <td v-bind:id="board.goal_position">
                        Goal
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
                <tr>
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
                <li v-for="member in members" class="list-group-item">
                    <i v-if="member.aicon" v-bind:class="'fas fa-2x fa-' + member.aicon"></i>
                    {{ member.name }}
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
                <button class="btn" v-if="canShowRollDiceButton()" @click="rollDice()">サイコロを振る</button>
                <div class="input-group mt-4" v-if="!is_started">
                    <div class="input-group-prepend">
                        <span class="input-group-text">招待URL</span>
                    </div>
                    <input class="copy form-control bg-white" type="text" v-model="join_url" :data-clipboard-text="join_url" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
<script>
export default {
    props: {
        board: Object,
        spaces: Object,
        room: Object,
        members: Array,
        auth_id: Number,
        room_status_open: Number,
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
            join_url: location.href + '/join'
        }
    },
    created: function () {
      this.col_count = (Number(this.board.goal_position) - 2) / 2;
    },
    mounted: function () {
        window.Echo.private('member-added-channel.' + this.room.id).listen('MemberAdded', response => {
            // response.userId
            // response.roomId
            // これを使ってユーザ名取得&this.membersに追加
        });

        window.Echo.private('sugoroku-started-channel.' + this.room.id).listen('SugorokuStarted', response => {
            this.logs.push('ゲームスタート！');
            this.gameStart();
        });

        window.Echo.private('dice-rolled-channel.' + this.room.id).listen('DiceRolled', response => {
            this.logs.push(this.getMemberName(response.userId) + 'さんがサイコロをふって' + response.number + '進みました');
            this.movePiece(response.userId, response.number);
        });
  },
  methods: {
    getMemberName: function (id) {
        for (let key in this.members) {
            if (this.members[key]['id']) {
                return this.members[key]['name'];
            }
        }
    },
    getSpaceName: function (id) { // 特殊マスの名前を返す
        if (this.spaces[id]) {
            return this.spaces[id].name;
        }
        return '';
    },
    gameStart: function () { // ゲーム開始準備
        // メンバー情報の一括更新
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.get('/api/sugoroku/members/' + this.room.id, {
            headers: {
                "Content-Type": "application/json"
            }
        }).then(function (response) {
            if (response.data.status === 'success') {
                this.members = response.data.members;
            }
        }).catch(function(error) {
            console.log(error);
        });


        // コマの初期設定
        this.piece_positions[1] = [];
        for (let key in this.members) {
            this.piece_positions[1].push({
                user_id: this.members[key]['id'],
                status: 1,
                aicon: this.piece_icons[key],
                go: this.members[key]['pivot']['go']
            });
            this.members[key]['aicon'] = this.piece_icons[key];
        }
        this.piece_positions[1].push({
            user_id: 0,
            status: 2,
            go: this.members.length + 1,
            aicon: this.virus_icon
        });
    },
    setPiece: function (position) { // マスにコマを配置する
        return this.piece_positions[position];
    },
    movePiece: function (user_id, move_num) { // コマを移動させる
        let piece_positions_tmp = {};
        for (let position in this.piece_positions) {
            let users = this.piece_positions[position];
            for (let key in users) {
                // ゴール済みのユーザは除外
                if (users[key]['status'] === 3) {
                    piece_positions_tmp[position].push(users[key]);
                    continue;
                }

                if (user_id === users[key]['user_id']) {
                    let new_position = parseInt(position) + parseInt(move_num);

                    // ゴール
                    if (new_position >= this.board.goal_position && users[key]['status'] === this.board.goal_status) {
                        new_position = this.board.goal_position;
                        users[key]['status'] = 3;
                    } else if (new_position > this.board.goal_position) {
                        new_position = new_position - this.board.goal_position;
                    }

                    // 特殊マス
                    for (let i = parseInt(position) + 1;i <= new_position; i++) {
                        if (this.spaces[i]) {
                            if (this.spaces[i]['effect_id'] === 1) {
                                users[key]['status'] = this.spaces[i]['effect_num'];
                            }
                        }
                    }

                    if (!Array.isArray(piece_positions_tmp[new_position])) {
                        piece_positions_tmp[new_position] = [];
                    }
                    piece_positions_tmp[new_position].push(users[key]);
                } else {
                    if (!Array.isArray(piece_positions_tmp[position])) {
                        piece_positions_tmp[position] = [];
                    }
                    piece_positions_tmp[position].push(users[key]);
                }
            }
        }
        this.piece_positions = piece_positions_tmp;
    },
    canShowStartButton: function () {
      if (this.room.owner_id === this.auth_id &&
            this.room.status === this.room_status_open) {
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
                console.log('ゲームをスタートしました');
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
            console.log(error);
        });
    },
    canShowRollDiceButton: function () {
        if (this.is_started) {
            return true;
        }
        return false;
    }
  }
}
</script>