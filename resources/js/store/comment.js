import { defineStore } from 'pinia'

import {getCommentsByItemId, postNewComment, deleteComment, getRepliesByCommentId, postNewReply, deleteReply, getCommentSingle, editComment, editReply } from '../api/comment'
import { toggleLikeItem } from '../api/likes';

export const useCommentStore = defineStore('comment', {
  // convert to a function
  state: () => ({
    commentsList: [],
    commentInfo: null
  }),
  actions: {
    doPushCommentsList({ response, page }) {
      if (page == 1) {
        this.commentsList = [];
      }
      let commentsList = window._.map(response.items, function (element) {
        return window._.extend({}, element, { replies: [] });
      });
      this.commentsList = window._.concat(this.commentsList, commentsList);
    },
    doAddComment(commentData) {
      commentData = window._.extend({}, commentData, { replies: [] })
      this.commentsList.unshift(commentData)
    },
    doToggleLikeCommentItem(commentInfo) {
      var comment = null
      if (this.commentInfo && this.commentInfo.id === commentInfo.subject_id) {
        comment = this.commentInfo
      } else {
        comment = window._.find(this.commentsList, { id: commentInfo.subject_id });
      }
      if(comment){
        if (commentInfo.action === 'add') {
          comment.is_liked = true;
          comment.like_count++;
        } else if (commentInfo.action === 'remove') {
          comment.is_liked = false;
          comment.like_count--;
        }
      }
    },
    doDeleteCommentItem(commentId) {
      if (this.commentInfo && this.commentInfo.id === commentId.id) {
        this.commentInfo = null
      } else {
        this.commentsList = this.commentsList.filter(comment => comment.id !== commentId.id);
      }
    },
    doPushRepliesList({ response, commentId, page }) {
      let comment = window._.find(this.commentsList, { id: commentId });
      if (this.commentInfo && this.commentInfo.id === commentId) {
        comment = this.commentInfo
      }
      if(comment){
        if (page == 1) {
          comment.replies = [];
        }
        comment.replies = window._.concat(response.items.reverse(), comment.replies);
      }
    },
    hideRepliesList(commentId) {
      let comment = window._.find(this.commentsList, { id: commentId });
      if (this.commentInfo) {
        comment = this.commentInfo
      }
      if(comment){
        comment.replies = []
      }
    },
    doAddReply({ response, commentId }) {
      let comment = window._.find(this.commentsList, { id: commentId });
      if (this.commentInfo && this.commentInfo.id === commentId) {
        comment = this.commentInfo
      }
      if(comment){
        comment.reply_count++
        comment.replies.push(response)
      }
    },
    doToggleLikeReplyItem({ replyData, commentId }) {
      if (this.commentInfo && this.commentInfo.id === commentId) {
        var comment = this.commentInfo
        comment.replies.map(reply => {
          if (reply.id === replyData.subject_id) {
            if (replyData.action === 'add') {
              reply.is_liked = true;
              reply.like_count++;
            } else if (replyData.action === 'remove') {
              reply.is_liked = false;
              reply.like_count--;
            }
          }
          return reply;
        });
        if (comment.reply.id === replyData.subject_id) {
          if (replyData.action === 'add') {
            comment.reply.is_liked = true;
            comment.reply.like_count++;
          } else if (replyData.action === 'remove') {
            comment.reply.is_liked = false;
            comment.reply.like_count--;
          }
        }
      }
      else if (this.commentsList.length > 0) {
        var commentsList = window._.find(this.commentsList, { id: commentId });
        commentsList.replies.map(reply => {
          if (reply.id === replyData.subject_id) {
            if (replyData.action === 'add') {
              reply.is_liked = true;
              reply.like_count++;
            } else if (replyData.action === 'remove') {
              reply.is_liked = false;
              reply.like_count--;
            }
          }
          return reply;
        });
      }
    },
    doDeleteReplyItem({ replyData, commentId }) {
      let comment = window._.find(this.commentsList, { id: commentId });
      if (this.commentInfo && this.commentInfo.id === commentId) {
        comment = this.commentInfo
        if(comment.reply.id === replyData.id){
          comment.reply = null
        }
      }
      if(comment){
        comment.reply_count--   
        comment.replies = comment.replies.filter(reply => reply.id !== replyData.id)
      }
    },
    doSetCommentSingle(commentInfo) {
      if (commentInfo) {
        this.commentInfo = window._.extend({}, commentInfo.comment, { reply: null, replies: [] })
        if (typeof commentInfo.reply === 'undefined') {
          commentInfo.reply = []
        }
        this.commentInfo.reply = commentInfo.reply
      }
    },
    resetCommentsData() {
      this.commentsList = [];
      this.commentInfo = null;
    },
    doUpdateComment(commentData) {
      let comment = window._.find(this.commentsList, { id: commentData.id });
      if (this.commentInfo && this.commentInfo.id === commentData.id) {
        comment = this.commentInfo
      }
      if(comment){
        comment.content = commentData.content
        comment.mentions = commentData.mentions
        comment.isEdited = true
      }
    },
    doUpdateReply({ replyData, commentId }) {
      if (this.commentInfo && this.commentInfo.id === commentId) {
        var comment = this.commentInfo
        if(comment){
          comment.replies.map(reply => {
            if (reply.id === replyData.id) {
              reply.content = replyData.content
              reply.mentions = replyData.mentions
              reply.isEdited = true
            }
            return reply;
          });
          if (comment.reply.id === replyData.id) {
            comment.reply.content = replyData.content
            comment.reply.mentions = replyData.mentions
            comment.reply.isEdited = true
          }
        }
      }
      else if (this.commentsList.length > 0) {
        var commentsList = window._.find(this.commentsList, { id: commentId });
        commentsList.replies.map(reply => {
          if (reply.id === replyData.id) {
            reply.content = replyData.content
            reply.mentions = replyData.mentions
            reply.isEdited = true
          }
          return reply;
        });
      }
    },
    async getCommentsListByItemId({ itemType, itemId, page }) {
      const response = await getCommentsByItemId(itemType, itemId, page)
      this.doPushCommentsList({ response, page })
      return response
    },
    async postComment(commentData) {
      const response = await postNewComment(commentData);
      this.doAddComment(response)
      return response
    },
    async toggleLikeCommentItem(commentInfo) {
      await toggleLikeItem(commentInfo)
      this.doToggleLikeCommentItem(commentInfo)
    },
    async deleteComment(commentId) {
      try {
        await deleteComment(commentId)
        this.doDeleteCommentItem(commentId)
      } catch (error) {
        console.log(error)
      }
    },
    async getRepliesByCommentId({ commentId, page, reply_id }) {
      const response = await getRepliesByCommentId(commentId, page)
      // Reply Detail Page
      if (reply_id) {
        response.items = response.items.filter(item => item.id != reply_id)
      }
      this.doPushRepliesList({ response, commentId, page })
      return response
    },
    async postReply(replyData) {
      const response = await postNewReply(replyData);
      const commentId = replyData.comment_id

      this.doAddReply({response, commentId })
    },
    async toggleLikeReplyItem(replyData) {
      try {
        await toggleLikeItem(replyData)
        const commentId = replyData.comment_id
        this.doToggleLikeReplyItem({ replyData, commentId })
      } catch (error) {
        console.log(error)
      }
    },
    async deleteReply(replyData) {
      try {
        await deleteReply(replyData)
        const commentId = replyData.comment_id
        this.doDeleteReplyItem({ replyData, commentId })
      } catch (error) {
        console.log(error)
      }
    },
    async getCommentSingleDetail({ type, itemId, commentId, replyId }) {
      const response = await getCommentSingle(type, itemId, commentId, replyId)
      this.doSetCommentSingle(response)
    },
    async editComment(commentData) {
      const response = await editComment(commentData)
      this.doUpdateComment(response)
    },
    async editReply(replyData) {
      const response = await editReply(replyData)
      const commentId = replyData.comment_id
      this.doUpdateReply({ replyData: response, commentId })
    }
  },
  persist: false
})