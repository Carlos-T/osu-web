/**
 *    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */
// tslint:disable:max-classes-per-file
import Message from 'models/chat/message';
import {PresenceJSON} from '../chat/chat-api-responses';
import DispatcherAction from './dispatcher-action';

export class ChatChannelSwitchAction implements DispatcherAction {
  constructor(public channelId: number) {
  }
}

export class ChatChannelPartAction implements DispatcherAction {
  constructor(public channelId: number) {
  }
}

export class ChatMessageSendAction implements DispatcherAction {
  constructor(public message: Message) {
  }
}

export class ChatMessageAddAction implements DispatcherAction {
  constructor(public message: Message) {
  }
}

export class ChatMessageUpdateAction implements DispatcherAction {
  constructor(public message: Message) {
  }
}

export class ChatPresenceUpdateAction implements DispatcherAction {
  constructor(public presence: PresenceJSON) {
  }
}
