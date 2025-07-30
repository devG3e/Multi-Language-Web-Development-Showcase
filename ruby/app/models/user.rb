class User < ApplicationRecord
  devise :database_authenticatable, :registerable,
         :recoverable, :rememberable, :validatable,
         :trackable, :lockable, :timeoutable

  has_many :posts, dependent: :destroy
  has_many :comments, dependent: :destroy
  has_many :activities, dependent: :destroy
  has_many :notifications, dependent: :destroy

  enum role: { user: 0, moderator: 1, admin: 2 }
  enum status: { active: 0, suspended: 1, banned: 2 }

  validates :username, presence: true, uniqueness: true, length: { minimum: 3, maximum: 30 }
  validates :email, presence: true, uniqueness: true, format: { with: URI::MailTo::EMAIL_REGEXP }
  validates :role, presence: true

  scope :recent, -> { order(created_at: :desc) }
  scope :active, -> { where(status: :active) }

  def admin?
    role == 'admin'
  end

  def moderator?
    role == 'moderator' || role == 'admin'
  end

  def full_name
    "#{first_name} #{last_name}".strip
  end

  def avatar_url
    avatar.attached? ? avatar : "https://ui-avatars.com/api/?name=#{username}&background=random"
  end

  def last_activity
    activities.last&.created_at || last_sign_in_at || created_at
  end

  def can_manage?(resource)
    return true if admin?
    return true if moderator? && resource.respond_to?(:user_id) && resource.user_id == id
    false
  end
end 