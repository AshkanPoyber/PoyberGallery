/**
 * PoyberGallery — Client-side interactions
 */

(() => {
  "use strict";

  const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.content || "";

  // ============ LIKE ============
  const likeBtn = document.getElementById("likeBtn");
  if (likeBtn) {
    likeBtn.addEventListener("click", async () => {
      const imageId = +likeBtn.dataset.imageId;
      const wasLiked = likeBtn.dataset.liked === "1";

      // Optimistic UI
      likeBtn.dataset.liked = wasLiked ? "0" : "1";
      const countEl = document.getElementById("likeCount");
      countEl.textContent = Math.max(
        0,
        +countEl.textContent + (wasLiked ? -1 : 1),
      );

      likeBtn.classList.toggle("bg-rose-500/15", !wasLiked);
      likeBtn.classList.toggle("text-rose-500", !wasLiked);
      likeBtn.classList.toggle("bg-black/5", wasLiked);
      likeBtn.classList.toggle("dark:bg-white/5", wasLiked);
      likeBtn.classList.toggle("text-slate-600", wasLiked);
      likeBtn.classList.toggle("dark:text-slate-300", wasLiked);
      likeBtn
        .querySelector("svg")
        .setAttribute("fill", !wasLiked ? "currentColor" : "none");

      try {
        const res = await fetch("api/like.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          credentials: "same-origin",
          body: JSON.stringify({
            image_id: imageId,
            csrf_token: csrfToken,
          }),
        });

        if (!res.ok) throw new Error("Request failed");

        const data = await res.json();
        if (data.success) {
          countEl.textContent = data.count;
          likeBtn.dataset.liked = data.liked ? "1" : "0";
        }
      } catch (err) {
        console.warn("Like failed, reverting", err);
        // Revert
        likeBtn.dataset.liked = wasLiked ? "1" : "0";
        countEl.textContent = Math.max(
          0,
          +countEl.textContent + (wasLiked ? 1 : -1),
        );
      }
    });
  }

  // ============ COMMENT FORM ============
  const commentForm = document.getElementById("commentForm");
  if (commentForm) {
    commentForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const imageId = +commentForm.dataset.imageId;
      const textEl = document.getElementById("commentText");
      const text = textEl.value.trim();
      if (!text) return;

      const submitBtn = commentForm.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = "Posting…";

      try {
        const res = await fetch("api/comment.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          credentials: "same-origin",
          body: JSON.stringify({
            image_id: imageId,
            text: text,
            csrf_token: csrfToken,
          }),
        });

        if (!res.ok) throw new Error("Failed");
        const data = await res.json();

        if (data.success) {
          addCommentToUI(data.comment);
          textEl.value = "";
          document.getElementById("commentEmpty")?.classList.add("hidden");
          updateCommentCount(1);
        }
      } catch (err) {
        alert("Could not post comment. Please try again.");
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = "Post";
      }
    });
  }

  function addCommentToUI(c) {
    const list = document.getElementById("commentList");
    if (!list) return;

    const li = document.createElement("li");
    li.className = "flex gap-3 text-sm";
    li.dataset.commentId = c.id;

    const initial = (c.username || "?")[0].toUpperCase();
    const date = new Date(c.created_at.replace(" ", "T"));
    const dateStr = date.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
    });

    li.innerHTML = `
      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-accent to-fuchsia-500 grid place-items-center text-white text-xs font-semibold flex-shrink-0">
        ${escapeHtml(initial)}
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-baseline gap-2">
          <a href="profile.php?u=${encodeURIComponent(c.username)}" class="font-medium text-slate-900 dark:text-slate-100 hover:text-accent-soft transition">
            @${escapeHtml(c.username)}
          </a>
          <time class="text-[11px] text-slate-500">${dateStr}</time>
          <button class="comment-delete ml-auto text-[11px] text-slate-400 hover:text-rose-500 transition" data-comment-id="${c.id}">
            Delete
          </button>
        </div>
        <p class="text-slate-700 dark:text-slate-300 mt-0.5 whitespace-pre-wrap break-words">${escapeHtml(c.text)}</p>
      </div>
    `;

    list.prepend(li);
  }

  // ============ DELETE COMMENT ============
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".comment-delete");
    if (!btn) return;

    if (!confirm("Delete this comment?")) return;

    const commentId = +btn.dataset.commentId;

    try {
      const res = await fetch("api/delete-comment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
        body: JSON.stringify({
          comment_id: commentId,
          csrf_token: csrfToken,
        }),
      });

      if (!res.ok) throw new Error("Failed");
      const data = await res.json();

      if (data.success) {
        document.querySelector(`li[data-comment-id="${commentId}"]`)?.remove();
        updateCommentCount(-1);
      }
    } catch (err) {
      alert("Could not delete comment.");
    }
  });

  function updateCommentCount(delta) {
    const el = document.getElementById("commentCount");
    if (!el) return;
    const current = +el.textContent.replace(/\D/g, "") || 0;
    const next = Math.max(0, current + delta);
    el.textContent = `(${next})`;
    document
      .getElementById("commentEmpty")
      ?.classList.toggle("hidden", next > 0);
  }

  function escapeHtml(s) {
    const div = document.createElement("div");
    div.textContent = s;
    return div.innerHTML;
  }

  // ============ FOLLOW ============
  const followBtn = document.getElementById("followBtn");
  if (followBtn) {
    followBtn.addEventListener("click", async () => {
      const userId = +followBtn.dataset.userId;
      const wasFollowing = followBtn.dataset.following === "1";

      // Optimistic UI
      followBtn.disabled = true;
      applyFollowStyle(!wasFollowing);

      try {
        const res = await fetch("api/follow.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          credentials: "same-origin",
          body: JSON.stringify({
            user_id: userId,
            csrf_token: csrfToken,
          }),
        });

        if (!res.ok) throw new Error("Failed");
        const data = await res.json();

        if (data.success) {
          followBtn.dataset.following = data.following ? "1" : "0";
          const fc = document.getElementById("followersCount");
          if (fc) fc.textContent = data.followers.toLocaleString();
        }
      } catch (err) {
        // Revert
        applyFollowStyle(wasFollowing);
      } finally {
        followBtn.disabled = false;
      }
    });

    function applyFollowStyle(following) {
      followBtn.dataset.following = following ? "1" : "0";
      followBtn.textContent = following ? "Following" : "Follow";

      // Reset all state classes
      followBtn.className =
        "px-5 py-2 rounded-xl text-sm font-medium transition";

      if (following) {
        followBtn.classList.add(
          "text-slate-700",
          "dark:text-slate-300",
          "bg-black/5",
          "dark:bg-white/5",
          "hover:bg-rose-500/15",
          "hover:text-rose-500",
        );
      } else {
        followBtn.classList.add(
          "text-white",
          "bg-gradient-to-br",
          "from-accent",
          "to-fuchsia-500",
          "hover:shadow-glow",
        );
      }
    }
  }
})();
