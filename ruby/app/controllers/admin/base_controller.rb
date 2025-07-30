class Admin::BaseController < ApplicationController
  before_action :authenticate_user!
  before_action :ensure_admin!
  layout 'admin'

  private

  def ensure_admin!
    unless current_user&.admin?
      redirect_to root_path, alert: 'Access denied. Admin privileges required.'
    end
  end

  def set_paper_trail_whodunnit
    user_for_paper_trail = current_user
  end
end 