/* tslint:disable:max-classes-per-file */

// importable coffeescript modules
declare module 'block-button' {
  interface Props {
    onClick?: () => void;
    modifiers?: string[];
    userId: number;
    wrapperClass?: string;
  }

  class BlockButton extends React.PureComponent<Props> {}
}

declare module 'big-button' {
  class BigButton extends React.PureComponent<any> {}
}

declare module 'flag-country' {
  class FlagCountry extends React.PureComponent<any> {}
}

declare module 'friend-button' {
  class FriendButton extends React.PureComponent<any> {}
}

declare module 'img2x' {
  class Img2x extends React.PureComponent<any> {}
}

declare module 'show-more-link' {
  class ShowMoreLink extends React.PureComponent<any> {}
}

declare module 'spinner' {
  interface Props {
    modifiers?: string[];
  }

  class Spinner extends React.PureComponent<Props> {}
}
declare module 'user-avatar' {
  class UserAvatar extends React.PureComponent<any> {}
}

declare module 'comments' {
  class Comments extends React.PureComponent<any> {}
}

declare module 'comments-manager' {
  interface Props {
    commentableType: string;
    commentableId: number;
    commentBundle: any;
    component: any;
    componentProps: any;
  }

  class CommentsManager extends React.PureComponent<Props> {}
}

declare module 'popup-menu' {
  type Children = (dismiss: () => void) => React.ReactFragment;

  interface Props {
    children: Children;
    onHide?: () => void;
    onShow?: () => void;
  }

  class PopupMenu extends React.PureComponent<Props, any> {}
}

declare module 'report-form' {
  interface ReportFormProps {
    allowOptions: boolean;
    completed: boolean;
    disabled: boolean;
    onClose: () => void;
    onSubmit: ({comments}: {comments: string}) => void;
    title: string;
    visible: boolean;
  }

  class ReportForm extends React.PureComponent<ReportFormProps, any> {}
}

declare module 'report-score' {
  interface Props {
    score: Score;
  }

  class ReportScore extends React.PureComponent<Props> {}
}

declare module 'report-user' {
  interface Props {
    onFormClose?: () => void;
    modifiers?: string[];
    user: User;
    wrapperClass?: string;
  }

  class ReportUser extends React.PureComponent<Props> {}
}
