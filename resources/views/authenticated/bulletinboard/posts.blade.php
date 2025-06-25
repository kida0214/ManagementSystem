<x-sidebar>
<div class="board_area w-100 border m-auto d-flex">
  <div class="post_view w-75 mt-5">
    <p class="w-75 m-auto">投稿一覧</p>
    @foreach($posts as $post)
    <div class="post_area border w-75 m-auto p-3">
      <p><span>{{ $post->user->over_name }}</span><span class="ml-3">{{ $post->user->under_name }}</span>さん</p>
      <p><a href="{{ route('post.detail', ['id' => $post->id]) }}">{{ $post->post_title }}</a></p>
      <div class="post_bottom_area d-flex">
        <div class="d-flex post_status">
          <div class="mr-5">
            <i class="fa fa-comment"></i>
            <span>{{ $post->postComments->count() }}</span>
          </div>
          <div>
            @if(Auth::user()->is_Like($post->id))
            <p class="m-0">
              <i class="fas fa-heart like-btn text-danger" data-post-id="{{ $post->id }}"></i>
              <span id="like-count-{{ $post->id }}">{{ $post->likes->count() }}</span>
            </p>
            @else
            <p class="m-0">
              <i class="far fa-heart like-btn" data-post-id="{{ $post->id }}"></i>
              <span id="like-count-{{ $post->id }}">{{ $post->likes->count() }}</span>
            </p>
            @endif
          </div>

        </div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="other_area border w-25">
    <div class="border m-4">
      <div><a href="{{ route('post.input') }}">投稿</a></div>

      <form action="{{ route('post.show') }}" method="get" id="postSearchRequest">
        <div>
          <input type="text" placeholder="キーワードを検索" name="keyword">
          <input type="submit" value="検索">
        </div>
        <input type="submit" name="like_posts" class="category_btn" value="いいねした投稿">
        <input type="submit" name="my_posts" class="category_btn" value="自分の投稿">

        <ul>
  @foreach($categories as $category)
  <li class="main_category_wrapper">
    <div class="d-flex align-items-center justify-content-between main_category_toggle" data-category-id="{{ $category->id }}" style="cursor: pointer;">
      <span>{{ $category->main_category }}</span>
      <i class="arrow-icon fas fa-chevron-down" id="arrow-{{ $category->id }}"></i>
    </div>

    <ul class="sub_category_list" id="sub-list-{{ $category->id }}" style="display: none; margin-left: 1rem;">
      @foreach($category->subCategories as $sub)
      <li>
        <button type="submit" name="category_word" value="{{ $sub->sub_category }}" class="btn btn-link p-0">
          {{ $sub->sub_category }}
        </button>
      </li>
      @endforeach
    </ul>
  </li>
  @endforeach
</ul>
      </form>
    </div>
  </div>
</div>

{{-- JavaScript for Like --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const postId = this.dataset.postId;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route("post.toggleLike") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ post_id: postId }),
            })
            .then(response => response.json())
            .then(data => {
                const countSpan = document.querySelector(`#like-count-${postId}`);
                countSpan.textContent = data.like_count;

                if (data.liked) {
                    this.classList.remove('far');
                    this.classList.add('fas', 'text-danger');
                } else {
                    this.classList.remove('fas', 'text-danger');
                    this.classList.add('far');
                }
            });
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // いいね処理（元からある部分）...

  // ▼ カテゴリ開閉処理
  document.querySelectorAll('.main_category_toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
      const categoryId = this.dataset.categoryId;
      const subList = document.getElementById(`sub-list-${categoryId}`);
      const arrow = document.getElementById(`arrow-${categoryId}`);

      if (subList.style.display === 'none' || subList.style.display === '') {
        subList.style.display = 'block';
        arrow.classList.remove('fa-chevron-down');
        arrow.classList.add('fa-chevron-up');
      } else {
        subList.style.display = 'none';
        arrow.classList.remove('fa-chevron-up');
        arrow.classList.add('fa-chevron-down');
      }
    });
  });
});
</script>
</x-sidebar>
