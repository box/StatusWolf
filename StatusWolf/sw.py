from flask import Flask, render_template, url_for, request, flash, redirect, g, session, jsonify
import flask.ext.login as flogin
import logging

from StatusWolf import default_log_handler, plugins
from StatusWolf.config import config
import StatusWolf.auth as auth
from StatusWolf.db import swdb
import StatusWolf.forms as forms

app = Flask(__name__)
app.config.update(config.app)
app.login_manager = flogin.LoginManager()
app.login_manager.login_view = 'sw_login'
app.login_manager.init_app(app)
if not app.debug:
    del app.logger.handlers[:]
    app.logger.setLevel(getattr(logging, config.logging['LOGLEVEL']))
    app.logger.addHandler(default_log_handler)


class SWFormParseError(Exception):
    """
    Exception class for errors parsing incoming form data
    """
    pass


@app.login_manager.user_loader
def load_user(id):
    return auth.User.get('id', id)


@app.route('/')
def sw_root():
    """
    Main index page for the application

    """
    return redirect(url_for('sw_root') + 'login')


@app.route('/login', methods=['GET', 'POST'])
def sw_login():
    """
    Login page for the application

    Returns:
        (redirect): URL for the main index page if the user
                    is already authenticate
        (str): Rendered login page template if the user
               is not authenticated

    """
    form = forms.LoginForm()
    if form.validate_on_submit():
        if 'username' in session:
            if session['username'] != form.username.data:
                app.logger.warning('session user mismatch, session user {0} != {1}'.format(
                    session.username,
                    form.username.data,
                ))
                session.pop('username', None)
                session.modified = True

    app.logger.debug(session)
    auth_error_message = None
    if 'auth_error' in session:
        auth_error_message = session.pop('auth_error', None)
        session.modified = True
        app.logger.error('Failed login attempt for {0}: {1}'.format(
            form.username.data,
            auth_error_message,
        ))
    if 'username' in session:
        app.logger.debug(
            'Login function called for already logged in user ({0}), resetting'.format(
                session['username']
            )
        )
        session.pop('username', None)
        flogin.logout_user()
        app.logger.debug(session)

    login_next = None
    if 'next' in request.args:
        login_next = '?next={0}'.format(request.args.get('next'))

    js = render_template('sw_login.js')
    return render_template('login.html.jinja2',
                           stylesheet_url=url_for('static', filename='css/sw_dark.css'),
                           login_css_url=url_for('static', filename='css/login.css'),
                           jquery=url_for('static', filename='js/jquery.min.js'),
                           bootstrap=url_for('static', filename='js/bootstrap.min.js'),
                           magnific=url_for('static', filename='js/magnific-popup.js'),
                           form=form,
                           auth_error_message=auth_error_message,
                           login_next=login_next,
                           main_js=js,)