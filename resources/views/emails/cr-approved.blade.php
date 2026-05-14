<x-mail::message>
    # 🎉 Welcome to Campus Buddy, {{ $user->name }}!

    Your **Class Representative (CR)** account has been reviewed and **officially approved** by the Campus Buddy Admin.

    You can now log in to Campus Buddy and access all your CR features.

    ---

    ### 📋 Your Account Details

    | Field | Details |
    |---|---|
    | **Name** | {{ $user->name }} |
    | **Student ID** | {{ $user->student_id }} |
    | **Department** | {{ $user->department }} |
    | **Batch** | {{ $user->batch }} |
    | **Section** | {{ $user->section }} |
    | **Role** | Class Representative (CR) |

    ---

    ### 🚀 What You Can Do as a CR

    - 📢 Post announcements directly to your classmates' dashboards
    - 📝 Create and manage assignments, quizzes, and presentations
    - 📅 Update and maintain the class routine (schedule)

    <x-mail::button :url="url('/login')" color="primary">
        Log In to Campus Buddy
    </x-mail::button>

    If you have any issues logging in, please contact your campus administrator.

    Thanks,<br>
    **The Campus Buddy Team**

    <x-mail::subcopy>
        This email was sent because you registered as a Class Representative on Campus Buddy.
        If you did not create this account, please ignore this email.
    </x-mail::subcopy>
</x-mail::message>