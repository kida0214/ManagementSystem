<x-sidebar>
  <p>ユーザー検索</p>

  <div class="search_content w-100 border d-flex">
    {{-- ユーザー一覧 --}}
    <div class="reserve_users_area">
      @foreach($users as $user)
        <div class="border one_person">
          <div>
            <span>ID : </span><span>{{ $user->id }}</span>
          </div>
          <div>
            <span>名前 : </span>
            <a href="{{ route('user.profile', ['id' => $user->id]) }}">
              <span>{{ $user->over_name }}</span>
              <span>{{ $user->under_name }}</span>
            </a>
          </div>
          <div>
            <span>カナ : </span>
            <span>({{ $user->over_name_kana }}</span>
            <span>{{ $user->under_name_kana }})</span>
          </div>
          <div>
            <span>性別 : </span>
            @if($user->sex == 1)
              <span>男</span>
            @elseif($user->sex == 2)
              <span>女</span>
            @else
              <span>その他</span>
            @endif
          </div>
          <div>
            <span>生年月日 : </span><span>{{ $user->birth_day }}</span>
          </div>
          <div>
            <span>権限 : </span>
            @if($user->role == 1)
              <span>教師(国語)</span>
            @elseif($user->role == 2)
              <span>教師(数学)</span>
            @elseif($user->role == 3)
              <span>講師(英語)</span>
            @else
              <span>生徒</span>
            @endif
          </div>

          {{-- 生徒の場合のみ選択科目を表示 --}}
          @if($user->role == 4)
            <div>
              <span>選択科目 : </span>
              @if($user->subjects->isNotEmpty())
                @foreach($user->subjects as $subject)
                  <span>{{ $subject->subject }}@if(!$loop->last)、@endif</span>
                @endforeach
              @else
                <span>未選択</span>
              @endif
            </div>
          @endif
        </div>
      @endforeach
    </div>

    {{-- 検索フォーム --}}
    <div class="search_area w-25 border">
      <div>
        <input type="text" class="free_word" name="keyword" placeholder="キーワードを検索" form="userSearchRequest">
      </div>

      <div>
        <label>カテゴリ</label>
        <select name="category" form="userSearchRequest">
          <option value="name">名前</option>
          <option value="id">社員ID</option>
        </select>
      </div>

      <div>
        <label>並び替え</label>
        <select name="updown" form="userSearchRequest">
          <option value="ASC">昇順</option>
          <option value="DESC">降順</option>
        </select>
      </div>

      <div>
        <p class="m-0 search_conditions"><span>検索条件の追加</span></p>
        <div class="search_conditions_inner">

          {{-- 性別 --}}
          <div>
            <label>性別</label>
            <span>男</span><input type="radio" name="sex" value="1" form="userSearchRequest">
            <span>女</span><input type="radio" name="sex" value="2" form="userSearchRequest">
            <span>その他</span><input type="radio" name="sex" value="3" form="userSearchRequest">
          </div>

          {{-- 権限 --}}
          <div>
            <label>権限</label>
            <select name="role" form="userSearchRequest" class="engineer">
              <option selected disabled>----</option>
              <option value="1">教師(国語)</option>
              <option value="2">教師(数学)</option>
              <option value="3">教師(英語)</option>
              <option value="4">生徒</option>
            </select>
          </div>

          {{-- 選択科目 --}}
          <div class="selected_engineer">
            <label>選択科目</label>
            <div>
              <span>国語</span>
              <input type="radio" name="subject" value="1" form="userSearchRequest">

              <span>数学</span>
              <input type="radio" name="subject" value="2" form="userSearchRequest">

              <span>英語</span>
              <input type="radio" name="subject" value="3" form="userSearchRequest">
            </div>
          </div>

        </div>
      </div>

      {{-- ボタン類 --}}
      <div>
        <input type="reset" value="リセット" form="userSearchRequest">
      </div>
      <div>
        <input type="submit" name="search_btn" value="検索" form="userSearchRequest">
      </div>

      <form action="{{ route('user.show') }}" method="get" id="userSearchRequest"></form>
    </div>
  </div>
</x-sidebar>
