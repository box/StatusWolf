from flask.ext.wtf import Form
from wtforms import StringField, PasswordField, HiddenField
from wtforms.validators import Required


class LoginForm(Form):
    username = StringField('username', validators=[Required()])
    password = PasswordField('password', validators=[Required()])
