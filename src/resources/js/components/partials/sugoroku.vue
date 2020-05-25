<template>
<div id="sugoroku">
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
    <a href="#" @click="movePiece(2, 10)">user2で10すすむ</a>
</div>
</template>
<script>
export default {
  props: {
      board: Object,
      spaces: Object
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
    }
  },
  created: function () {
      this.col_count = (Number(this.board.goal_position) - 2) / 2;

      this.piece_positions = {
        1: [
            {
                user_id: 1,
                status: 1,
                aicon: this.piece_icons[0],
            },
            {
                user_id: 2,
                status: 2,
                aicon: this.piece_icons[4],
            },
            {
                user_id: 0,
                status: 2,
                aicon: this.virus_icon,
            }
        ]
      }; // TODO: 他の実装に合わせてデータ構成調整
   },
  methods: {
    getSpaceName: function (id) {
        if (this.spaces[id]) {
            return this.spaces[id].name;
        }
        return '';
    },
    setPiece: function (position) {
        return this.piece_positions[position];
    },
    movePiece: function (user_id, move_num) {
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
                        console.log(i);
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
    }
  }
}
</script>
